/* capanalysis.c
 *
 * Capture Analysis
 *
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2012-16 Gianluca Costa. Web: www.capanalysis.net
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <signal.h>
#include <sys/wait.h>
#include <signal.h>
#include <pthread.h>
#include <pcap.h>

#include "capanalysis.h"
#include "session_decoding.h"
#include "dbinterface.h"
#include "version.h"
#include "config_file.h"
#include "log.h"

#include "pkginstall.h"
# define ROOT_USER    1

static volatile bool terminate;
static pthread_mutex_t pd_mux;           /* mutex to access atomicly the tbl */
static dsdec * volatile ds_tbl;
static volatile int dim;
static bool md5en;


static void Usage(char *name)
{
    printf("\n");
    printf("usage: %s [-v] {-c <config_file> | {-d <root_dir> -b <db_type>}} [-h]\n", name);
    printf("\t-v version\n");
    printf("\t-c config file\n");
    printf("\t-d dataset root dir\n");
    printf("\t-b DB type (postgresql or sqlite)\n");
    printf("\t-h this help\n");
    printf("\n");
}


static void CapanaDecInit(dsdec *dec)
{
    memset(dec, 0, sizeof(dsdec));
    dec->ds_id = -1;
    dec->run = FALSE;
    dec->end = FALSE;
    dec->rm = FALSE;
    dec->name[0] = '\0';
    dec->size = 0;
    dec->filenum = 0;
    dec->rt = FALSE;
    dec->sd[0] = -1;
    dec->sd[1] = -1;
    dec->deep.tp = CA_DP_NONE;
}


int CapanaHash(const char *path_src, char *md5, char *sha1)
{
    char cmd[2*CA_FILENAME_PATH];
    char buffer[2*CA_HASH_STR];
    char dummy[CA_HASH_STR];
    FILE *fp;
    int res, ret = 0;
    
    /* run md5sum and sha1sum */
    sprintf(cmd, "md5sum \"%s\" > /tmp/capana_hash.txt; sha1sum \"%s\" >> /tmp/capana_hash.txt", path_src, path_src);
    ret = system(cmd);
    fp = fopen("/tmp/capana_hash.txt", "r");
    if (fp != NULL) {
        if (fgets(buffer, 2*CA_HASH_STR, fp) != NULL) {
            /* md5 */
            res = sscanf(buffer, "%s %s", md5, dummy);
            if (res != 2) {
                ret = -1;
            }

            /* sha1 */
            if (fgets(buffer, 2*CA_HASH_STR, fp) != NULL) {
                res = sscanf(buffer, "%s %s", sha1, dummy);
                if (res != 2) {
                    ret = -1;
                }
            }
            else {
                ret = -1;
            }
        }
        else {
            ret = -1;
        }
        
        fclose(fp);
    }
    else {
        ret = -1;
    }
    
    remove("/tmp/capana_hash.txt");

    return ret;
}


static void CapanaSigTerm(int sig)
{
    terminate = TRUE;
}


bool CapCheck(char *file_name)
{
    pcap_t *cap;
    char errbuf[PCAP_ERRBUF_SIZE];

    cap = pcap_open_offline(file_name, errbuf);
    if (cap == NULL) {
        LogPrintf(LV_WARNING, "Error: %s", errbuf);
        return FALSE;
    }
    pcap_close(cap);

    return TRUE;
}


static int CapanaLoop(dbconf *db_c, char *root, time_t twpcap)
{
    dsdec *nds_tbl;
    int num, i, j, ret, fnd;
    struct stat info;
    char *filename, *tmp;
    char dir[CA_FILENAME_PATH];
    char path_src[CA_FILENAME_PATH];
    char path_dst[CA_FILENAME_PATH];
    char cmd[CA_FILENAME_PATH];
    char interf[CA_FILENAME_PATH];
    char filter[CA_FILTER_LINE];
    pid_t chld;
    char sha1[CA_HASH_STR];
    char md5[CA_HASH_STR];
    FILE *fp;
    time_t time_now;
    bool one_file, cc, skip_sleep;
    unsigned long pf_id;
    
    ds_tbl = malloc(sizeof(dsdec)*CA_TBL_ADD);
    for (i=0; i!=CA_TBL_ADD; i++) {
        CapanaDecInit(&(ds_tbl[i]));
    }
    dim = CA_TBL_ADD;
    num = 0;
    pf_id = 0;
    fnd = CA_FIND_DS;
    ret = 0;
    ret = ret;
    
    /* main loop */
    while (1) {
        skip_sleep = FALSE;
        /* find new dataset */
        if (fnd-- == 0) {
            fnd = CA_FIND_DS;
            pthread_mutex_lock(&pd_mux);
            i = SeDeFind(root, ds_tbl, dim);
            pthread_mutex_unlock(&pd_mux);
            if (i > 0) {
                num += i;
                if (dim-num < CA_TBL_ADD) {
                    pthread_mutex_lock(&pd_mux);
                    nds_tbl = realloc(ds_tbl, sizeof(dsdec)*(dim+CA_TBL_ADD));
                    if (nds_tbl != NULL) {
                        ds_tbl = nds_tbl;
                        memset(ds_tbl+dim, -1, sizeof(dsdec)*CA_TBL_ADD);
                        for (i=dim; i!= dim+CA_TBL_ADD; i++) {
                            CapanaDecInit(&(ds_tbl[i]));
                        }
                        dim += CA_TBL_ADD;
                    }
                    pthread_mutex_unlock(&pd_mux);
                }
            }
        }
        
        /* check file to decode */
        for (i=0, j=0; j<num; i++) {
            if (ds_tbl[i].ds_id == -1)
                continue;
            j++;
            /* if not rt decoding */
            if (ds_tbl[i].rt == TRUE)
                continue;

            /* check old files (from a crash) */
            if (ds_tbl[i].run == FALSE) {
                filename = SeDeFileDecode(root, ds_tbl[i].ds_id);
                if (filename != NULL) {
                    /* start decoding */
                    if (SeDeStart(db_c, root, ds_tbl[i].ds_id, &ds_tbl[i].pid, FALSE, NULL, NULL) == 0) {
                        /* change status in to db */
                        ds_tbl[i].run = TRUE;
                        ds_tbl[i].end_to = -1;
                    }
                    else {
                        LogPrintf(LV_FATAL, "Applications executions error");
                        exit(-1);
                    }
                }
            }

            /* check new files */
            filename = NULL;
            if (ds_tbl[i].deep.tp != CA_DP_EL || ds_tbl[i].deep.el > time(NULL))
                filename = SeDeFileNew(root, ds_tbl[i].ds_id, &one_file);
            if (filename != NULL && ds_tbl[i].end == FALSE) {
                /* check file name */
                time_now = time(NULL);
                if (strcmp(filename, ds_tbl[i].name) != 0) {
                    ds_tbl[i].size = 0;
                    strcpy(ds_tbl[i].name, filename);
                    ds_tbl[i].growth = time_now + CA_GROWTH_TO;
                    if (ds_tbl[i].run == FALSE) {
                        /* change status in to db */
                    }
                }
                if (one_file == FALSE) {
                    ds_tbl[i].growth = 0;
                }
                
                sprintf(dir, CA_NEW_DIR, root, ds_tbl[i].ds_id);
                sprintf(path_src, "%s/%s", dir, filename);
                
                /* check file growth */
                if (time_now < ds_tbl[i].growth)
                    time_now = 0;
                
                if (time_now && stat(path_src, &info) == 0) {
                    if (ds_tbl[i].size == info.st_size &&
                        (one_file == FALSE || SeDeFileActive(path_src) == FALSE)) {
                        skip_sleep = TRUE;
                        /* capture time update */
                        cc = CapCheck(path_src);
                        if (cc) {
                            pf_id = 0;
                            /* insert/update data in DB */
                            DBIntFilePcap(ds_tbl[i].ds_id, ds_tbl[i].size, ds_tbl[i].name, NULL, NULL, &pf_id);
                            if (pf_id == 0) {
                                LogPrintf(LV_WARNING, "Inset DB error!");
                                DBIntFilePcap(ds_tbl[i].ds_id, ds_tbl[i].size, ds_tbl[i].name, NULL, NULL, &pf_id);
                            }
                            /* checksum/hash */
                            if (md5en) {
                                if (CapanaHash(path_src, md5, sha1) != 0) {
                                    /* empty hash */
                                    md5[0] = '\0';
                                    sha1[0] = '\0';
                                }
                                
                                /* insert/update data in DB */
                                DBIntFilePcap(ds_tbl[i].ds_id, ds_tbl[i].size, ds_tbl[i].name, md5, sha1, &pf_id);
                            }
                        }
                        else {
                            LogPrintf(LV_ERROR, "Error: incorrect capture file %s", filename);
                            /* free */
                            ds_tbl[i].name[0] = '\0';
                            ds_tbl[i].size = 0;
                            ds_tbl[i].growth = 0;
                            if (ds_tbl[i].run == FALSE) {
                                /* change status in to db */
                            }
                            /* remove file */
                            remove(path_src);
                            continue;
                        }
                        /* free */
                        ds_tbl[i].name[0] = '\0';
                        ds_tbl[i].size = 0;
                        ds_tbl[i].growth = 0;
                        
                        /* move file in raw */
                        sprintf(dir, CA_RAW_DIR, root, ds_tbl[i].ds_id);
                        sprintf(path_dst, "%s/%08lu", dir, pf_id);
                        rename(path_src, path_dst);
                        chmod(path_dst, S_IRUSR | S_IWUSR | S_IRGRP | S_IWGRP | S_IROTH | S_IWOTH);

                        /* link file in decode dir */
                        strcpy(path_src, path_dst);
                        sprintf(dir, CA_DECOD_DIR, root, ds_tbl[i].ds_id);
                        sprintf(path_dst, "%s/%08lu", dir, pf_id);
                        ret = link(path_src, path_dst);
                        ds_tbl[i].filenum++;

                        /* start decoding */
                        if (ds_tbl[i].run == FALSE) {
                            if (SeDeStart(db_c, root, ds_tbl[i].ds_id, &ds_tbl[i].pid, FALSE, NULL, NULL) == 0) {
                                /* change status in to db */
                                ds_tbl[i].run = TRUE;
                                ds_tbl[i].end_to = -1;
                            }
                            else {
                                if (ds_tbl[i].run == FALSE) {
                                    /* change status in to db */
                                }
                                LogPrintf(LV_FATAL, "Applications executions error");
                                exit(-1);
                            }
                        }
                        
                        /* exist new file? */
                        filename = SeDeFileNew(root, ds_tbl[i].ds_id, &one_file);
                        if (filename != NULL) {
                            strcpy(ds_tbl[i].name, filename);
                            sprintf(dir, CA_NEW_DIR, root, ds_tbl[i].ds_id);
                            sprintf(path_src, "%s/%s", dir, filename);
                            if (stat(path_src, &info) == 0) {
                                ds_tbl[i].size = info.st_size;
                            }
                        }
                    }
                    else {
                        ds_tbl[i].size = info.st_size;
                        ds_tbl[i].growth = time_now + CA_GROWTH_TO;
                    }
                }
                else {
                    if (time_now)
                        perror("");
                }
            }
            else if (ds_tbl[i].end == FALSE && ds_tbl[i].run == TRUE) {
                /* there isn't a new file */
                time_now = time(NULL);
                if (ds_tbl[i].name[0] != '\0') { /* last file found */
                    ds_tbl[i].name[0] = '\0';
                    ds_tbl[i].growth = time_now + twpcap;
                }
                if (time_now > ds_tbl[i].growth) {
                    SeDeEnd(root, ds_tbl[i].ds_id, &ds_tbl[i].pid);
                    ds_tbl[i].end = TRUE;
                    ds_tbl[i].end_to = -1;
                }
            }
        }
        
        /* check dataset deletion */
        for (i=0, j=0; j<num; i++) {
            if (ds_tbl[i].ds_id == -1)
                continue;
            j++;
            sprintf(path_src, CA_DS_DIR"/%s", root, ds_tbl[i].ds_id, CA_DELETE_DS);
            if (stat(path_src, &info) == 0 || ds_tbl[i].rm == TRUE) {
                /* delete pol (case) */
                SeDeKill(ds_tbl, i);
                /* delete records in DB */
                DBIntDeleteDataSet(ds_tbl[i].ds_id);
                remove(path_src); /* this frees the XI */
                /* delete files */
                sprintf(cmd, "rm -rf "CA_DS_DIR, root, ds_tbl[i].ds_id);
                ret = system(cmd);
                /* remove samba shared folder */
                sprintf(cmd, CA_SAMBA_RM_DS, ds_tbl[i].ds_id, ds_tbl[i].ds_id);
                ret = system(cmd);
                ret = system(CA_SAMBA_RESTART_SERVICE);
                ret = system(CA_SAMBA_RESTART_SYSTEMCTL);
                
                CapanaDecInit(&(ds_tbl[i]));
                num--;
            }
        }

        /* check realtime dataset -start/stop- */
        for (i=0, j=0; j<num; i++) {
            if (ds_tbl[i].ds_id == -1)
                continue;
            j++;
            
            /* stop */
            sprintf(path_dst, CA_DS_DIR"/%s", root, ds_tbl[i].ds_id, CA_RT_STOP_FILE);
            if (stat(path_dst, &info) == 0) {
                /* stop rt acquisition */
                SeDeEnd(root, ds_tbl[i].ds_id, &ds_tbl[i].pid);
                ds_tbl[i].end = TRUE;
                ds_tbl[i].rt = FALSE;
                sprintf(path_src, CA_DS_DIR"/%s", root, ds_tbl[i].ds_id, CA_RT_START_FILE);
                remove(path_src);
                remove(path_dst);
            }
            if (ds_tbl[i].run == TRUE)
                continue;
            
            /* start */
            sprintf(path_src, CA_DS_DIR"/%s", root, ds_tbl[i].ds_id, CA_RT_START_FILE);
            if (stat(path_src, &info) == 0) {
                /* netework interface */
                fp = fopen(path_src, "r");
                if (fp != NULL) {
                    /* network intrface */
                    if (fgets(interf, CA_FILENAME_PATH, fp) != NULL) {
                        if ((tmp = strchr(interf, '\r')) != NULL) {
                            tmp[0] = '\0';
                        }
                        if ((tmp = strchr(interf, ' ')) != NULL) {
                            tmp[0] = '\0';
                        }
                        if ((tmp = strchr(interf, '\n')) != NULL) {
                            tmp[0] = '\0';
                        }
                        /* filter */
                        if (fgets(filter, CA_FILENAME_PATH, fp) != NULL) {
                            if ((tmp = strchr(filter, '\r')) != NULL) {
                                tmp[0] = '\0';
                            }
                            if ((tmp = strchr(filter, '\n')) != NULL) {
                                tmp[0] = '\0';
                            }
                        }
                        else {
                            filter[0] = '\0';
                        }
                        fclose(fp);

                        /* start rt acquisition */
                        if (SeDeStart(db_c, root, ds_tbl[i].ds_id, &ds_tbl[i].pid, TRUE, interf, filter) == 0) {
                            /* change status in to db */
                            ds_tbl[i].run = TRUE;
                            ds_tbl[i].rt = TRUE;
                            ds_tbl[i].end_to = -1;
                        }
                        else {
                            remove(path_src);
                            LogPrintf(LV_FATAL, "Applications executions error");
                            exit(-1);
                        }
                    }
                    else {
                        remove(path_src);
                        LogPrintf(LV_ERROR, "Interface error");
                    }
                }
                else {
                    remove(path_src);
                }
            }
        }

        /* check end timeout */
        for (i=0, j=0; j<num; i++) {
            if (ds_tbl[i].ds_id == -1)
                continue;
            j++;
            if (ds_tbl[i].end == TRUE && ds_tbl[i].run == TRUE) {
                if (ds_tbl[i].end_to == 0) {
                    /* force kill all task */
                    SeDeKill(ds_tbl, i);
                    ds_tbl[i].end_to = -1;
                }
                else if (ds_tbl[i].end_to != -1)
                    ds_tbl[i].end_to--;
            }
        }

        /* check depth */
        for (i=0, j=0; j<num; i++) {
            if (ds_tbl[i].ds_id == -1)
                continue;
            j++;
            switch (ds_tbl[i].deep.tp) {
            case CA_DP_EL:
                if (ds_tbl[i].deep.el < time(NULL) && ds_tbl[i].run == FALSE) {
                    /* remove all data */
                    LogPrintf(LV_INFO, "Remove dataset: %s [%i]", ds_tbl[i].name, ds_tbl[i].ds_id);
                    ds_tbl[i].rm = TRUE;
                }
                break;
                
            case CA_DP_TD:
                break;
                
            case CA_DP_FD:
                break;
                
            case CA_DP_SZ:
                break;

            default:
                break;
            }
        }
        
        /* check termination */
        if (terminate == TRUE) {
            for (i=0; i!=dim; i++) {
                SeDeKill(ds_tbl, i);
            }
            ret = system("killall xplico 2>/dev/null 1>/dev/null");
            break; /* exit from main cicle */
        }


        /* check process termination */
        do {
            chld = waitpid(0, NULL, WNOHANG);
            if (chld > 0) {
                for (i=0, j=0; j<num; i++) {
                    if (ds_tbl[i].ds_id == -1)
                        continue;
                    j++;
                    if (ds_tbl[i].run == TRUE) {
                        if (SeDeRun(&ds_tbl[i].pid, chld, TRUE) == 0) {
                            filename = SeDeFileDecode(root, ds_tbl[i].ds_id);
                            if (ds_tbl[i].end && filename == NULL) {
                                ds_tbl[i].end_to = CA_END_TO;
                                ds_tbl[i].pid.tot--;
                            }
                            else {
                                /* force the end, with kill */
                                ds_tbl[i].pid.tot--;
                                ds_tbl[i].end = TRUE;
                                ds_tbl[i].end_to = 1; /* without timeout */
                                SeDeKill(ds_tbl, i);
                                LogPrintf(LV_WARNING, "Xplico or a Manipulator is dead!");
                            }

                            if (ds_tbl[i].pid.tot == 0) {
                                ds_tbl[i].run = FALSE;
                                ds_tbl[i].end = FALSE;
                                ds_tbl[i].rt = FALSE;
                                ds_tbl[i].end_to = -1;
                                /* change status in to db */

                                /* pcap with fault.
                                   We suppose that the last file (the first in the dir)
                                   can do to crash Xplico */
                                if (filename != NULL) {
                                    LogPrintf(LV_WARNING, "Xplico is dead!");
                                    /* move file in fault dir */
                                    sprintf(dir, CA_DECOD_DIR, root, ds_tbl[i].ds_id);
                                    sprintf(path_src, "%s/%s", dir, filename);
                                    sprintf(dir, CA_FAULT_DIR, root, ds_tbl[i].ds_id);
                                    sprintf(path_dst, "%s/%s", dir, filename);
                                    rename(path_src, path_dst);
                                }
                            }
                        }
                    }
                }
            }
            else {
                chld = 0;
            }
        } while (chld);
        
        /* wait */
        if (skip_sleep == FALSE)
            sleep(1); /* tick, if you change it then change also all the timeout: CA_END_TO, CA_FIND_DS, ... */
    }
    
    /* free memory */
    free(ds_tbl);
    
    return 0;
}


int CfgParIsComment(char *line)
{
    char *cmnt;

    cmnt = strchr(line, CFG_LINE_COMMENT);
    if (cmnt == NULL)
        return 0;
    while (*line != CFG_LINE_COMMENT) {
        if (*line != ' ')
            return 0;
        line++;
    }
    
    return 1;
}


int CfgParamStr(const char *cfg_file, const char *rparam, char *ret_val, int rsize)
{
    FILE *fp;
    char buffer[CFG_LINE_MAX_SIZE];
    char bufcpy[CFG_LINE_MAX_SIZE];
    char scans[CFG_LINE_MAX_SIZE];
    char prm[CFG_LINE_MAX_SIZE];
    char *param;
    int res, ret;

    if (cfg_file == NULL)
        return -1;
        
    ret = -1;
    /* configuration file is without errors! */
    fp = fopen(cfg_file, "r");
    sprintf(scans, "%s=%s", rparam, "%s %s");
    while (fgets(buffer, CFG_LINE_MAX_SIZE, fp) != NULL) {
        /* check if line is a comment */
        if (!CfgParIsComment(buffer)) {
            param = buffer;
            while (param[0] == ' ')
                param++;
            if (param[0] != '\0') {
                res = sscanf(param, scans, prm, bufcpy);
                if (res > 0) {
                    if (strlen(prm) > rsize) {
                        LogPrintf(LV_ERROR, "Config file parameter (%s) to big", rparam);
                    }
                    else {
                        strcpy(ret_val, prm);
                        ret = 0;
                    }
                    break;
                }
            }
        }
    }

    fclose(fp);
    
    return ret;
}


static int ReadConfigFile(char *path, dbconf *db_c, char *root_dir, time_t *twpcap, char *cert)
{
    FILE *fp;
    int res, nl;
    char buffer[CFG_LINE_MAX_SIZE];
    char bufcpy[CFG_LINE_MAX_SIZE];
    char dbts[CFG_LINE_MAX_SIZE];
    char *param;
    int m5sum;
    bool root = FALSE;

    if (root_dir[0] != '\0')
        root = TRUE;
    
    fp = fopen(path, "r");
    if (fp == NULL) {
        LogPrintf(LV_WARNING, "Config file \"%s\" can't be opened", path);
        return -1;
    }
    nl = 0;
    dbts[0] = '\0';
    m5sum = 1;
    memset(db_c, '\0', sizeof(dbconf));
    while (fgets(buffer, CFG_LINE_MAX_SIZE, fp) != NULL) {
        nl++;
        /* check all line */
        if (strlen(buffer)+1 == CFG_LINE_MAX_SIZE) {
            LogPrintf(LV_WARNING,"Config file line more length to %d characters", CFG_LINE_MAX_SIZE);
            return -1;
        }
        /* check if line is a comment */
        if (!CfgParIsComment(buffer)) {
            param = strstr(buffer, CFG_PAR_PCAP_FILES_TIME);
            if (param != NULL) {
                res = sscanf(param, CFG_PAR_PCAP_FILES_TIME"=%lu %s", twpcap, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_TYPE);
            if (param != NULL) {
                if (dbts[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_TYPE);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_TYPE"=%s %s", dbts, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_FILE_NAME);
            if (param != NULL) {
                if (db_c->file[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_FILE_NAME);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_FILE_NAME"=%s %s", db_c->file, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_ROOT_DIR);
            if (param != NULL) {
                if (root_dir[0] != '\0' && root == FALSE) {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_ROOT_DIR);
                    return -1;
                }
                root = FALSE;
                res = sscanf(param, CFG_PAR_ROOT_DIR"=%s %s", root_dir, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_SSL_CERT);
            if (param != NULL) {
                if (cert[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_SSL_CERT);
                    return -1;
                }
                res = sscanf(param, CFG_SSL_CERT"=%s %s", cert, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_HOST);
            if (param != NULL) {
                if (db_c->host[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_HOST);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_HOST"=%s %s", db_c->host, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_NAME);
            if (param != NULL) {
                if (db_c->name[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_NAME);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_NAME"=%s %s", db_c->name, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_USER);
            if (param != NULL) {
                if (db_c->user[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_USER);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_USER"=%s %s", db_c->user, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_DB_PASSWORD);
            if (param != NULL) {
                if (db_c->password[0] != '\0') {
                    LogPrintf(LV_ERROR, "Config param error: param '%s' defined two times", CFG_PAR_DB_PASSWORD);
                    return -1;
                }
                res = sscanf(param, CFG_PAR_DB_PASSWORD"=%s %s", db_c->password, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
            }
            param = strstr(buffer, CFG_PAR_MD5_ENABLED);
            if (param != NULL) {
                res = sscanf(param, CFG_PAR_MD5_ENABLED"=%i %s", &m5sum, bufcpy);
                if (res > 0) {
                    if (res == 2 && !CfgParIsComment(bufcpy)) {
                        LogPrintf(LV_ERROR, "Config param error in line %d. Unknow param: %s", nl, bufcpy);
                        return -1;
                    }
                }
                if (m5sum) {
                    md5en = TRUE;
                }
                else {
                    md5en = FALSE;
                }
            }
        }
    }
    fclose(fp);
    
    /* check data */
    if (dbts[0] == '\0') {
        LogPrintf(LV_ERROR, "Config file without DB type [%s]", CFG_PAR_DB_TYPE);
        return -1;
    }
    else if (strcmp(dbts, DB_T_POSTGRES) == 0) {
        db_c->type = DB_POSTGRESQL;
    }
    else {
        LogPrintf(LV_ERROR, "Unknown DB type: %s", dbts);
        return -1;
    }

    switch (db_c->type) {
    case DB_POSTGRESQL:
        if (db_c->name[0] == '\0' ||
            db_c->user[0] == '\0' ||
            db_c->password[0] == '\0' ||
            db_c->host[0] == '\0') {
            LogPrintf(LV_ERROR, "Config file error. DB Postgresql requires: %s, %s, %s and %s", CFG_PAR_DB_HOST, CFG_PAR_DB_NAME, CFG_PAR_DB_USER, CFG_PAR_DB_PASSWORD);
            return -1;
        }
        break;
    }

    return 0;
}


static int CngLineFile(char *file, char *pattern, char *newline)
{
    char nfl[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    FILE *fin, *fout;
    int cmpl;

    cmpl = 1;
    sprintf(nfl, "%s.cng", file);
    fout = fopen(nfl, "w+");
    if (fout != NULL) {
        fin = fopen(file, "r");
        if (fin != NULL) {
            while (fgets(tmp, CA_FILENAME_PATH, fin)) {
                if (strstr(tmp, pattern) != NULL) {
                    fwrite(newline, 1, strlen(newline), fout);
                }
                else {
                    fwrite(tmp, 1, strlen(tmp), fout);
                }
            }
            fclose(fin);
            cmpl = 0;
        }
        fclose(fout);
    }
    rename(nfl, file);

    return cmpl;
}


static int UInstall(char *root)
{
    char cfg_file[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    int ret;

    ret = 0;
    ret = ret;
    
    /* remove all cache */
    sprintf(tmp, "rm -f %s/www/app/tmp/sessions/*", root);
    ret = system(tmp);
    sprintf(tmp, "rm -f %s/www/app/tmp/cache/*/*", root);
    ret = system(tmp);
    sprintf(tmp, "rm -f %s/www/app/tmp/logs/*", root);
    ret = system(tmp);
    sprintf(tmp, "rm -f %s/www/app/tmp/tests/*", root);
    ret = system(tmp);
    
    /* change the root path */
    sprintf(cfg_file, "%s/www/capinstall/app/config/config.php", root);
    sprintf(tmp, "$ROOT_DIR='%s';\n", root);
    CngLineFile(cfg_file, "$ROOT_DIR='", tmp);

    return 0;
}


static int UIConfig(dbconf *conf, char *root)
{
    char cfg_file[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    int ret;
    
    /* change the root path */
    sprintf(cfg_file, "%s/www/app/Config/core.php", root);
    sprintf(tmp, "Configure::write('Dataset.root', '%s');\n", root);
    CngLineFile(cfg_file, "Dataset.root", tmp);
        
    /* chage DB user and password */
    switch (conf->type) {
    case DB_POSTGRESQL:
        sprintf(tmp, "cp %s/www/app/Config/database.php_postgres %s/www/app/Config/database.php", root, root);
        ret = system(tmp);
        break;
    }
    
    if (conf->type == DB_POSTGRESQL) {
        sprintf(cfg_file, "%s/www/app/Config/database.php", root);
        sprintf(tmp, "		'password' => '%s',\n", conf->password);
        CngLineFile(cfg_file, "password", tmp);
    }
    
    return 0;
}


int DBCngUserPwd(dbconf *conf, char *root)
{
    char cmd[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    char cfg_file[CA_FILENAME_PATH];
    char password[DBCFG_BUFF_SIZE];
    unsigned long p;
    int ret;

    /* a new passowrd */
    p = (unsigned long)(((float)(rand())/RAND_MAX)*(time(NULL)));
    sprintf(password, "%lX", p);

    if (conf->type == DB_POSTGRESQL) {
        strcpy(conf->password, password);
        sprintf(cfg_file, "%s/db/postgresql/cngpwd.sql", root);
        sprintf(tmp, "CREATE USER capana WITH PASSWORD '%s' CREATEDB;\n", conf->password);
        CngLineFile(cfg_file, "CREATE USER", tmp);
        sprintf(tmp, "ALTER USER capana WITH PASSWORD '%s';\n", conf->password);
        CngLineFile(cfg_file, "ALTER USER", tmp);
        sprintf(cmd, "sudo -u %s psql -f %s", POSTGRESQL_USER, cfg_file);
        ret = system(cmd);
        if (ret) {
            ret = 0;
        }
        
        /* capanalysis config file */
        sprintf(cfg_file, "%s/cfg/canalysis.cfg", root);
        sprintf(tmp, "DB_PASSWORD=%s\n", conf->password);
        CngLineFile(cfg_file, "DB_PASSWORD=", tmp);
    }
    
    return 0;
}


static int DBCreate(dbconf *conf, char *root)
{
    char cmd[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    char cfg_file[CA_FILENAME_PATH];
    int ret;

    if (conf->type == DB_POSTGRESQL) {
        sprintf(cfg_file, "%s/db/postgresql/create_db.sql", root);
        sprintf(tmp, "%s/db/postgresql/", root);
        sprintf(cmd, "cd %s; sudo -u %s psql -f %s", tmp, POSTGRESQL_USER, cfg_file);
        ret = system(cmd);
        if (ret) {
            ret = 0;
        }
    }

    return 0;
}


static int DBUpgrade(dbconf *conf, char *root)
{
    char cmd[CA_FILENAME_PATH];
    char dbver[CA_FILENAME_PATH];
    char tmp[CA_FILENAME_PATH];
    char cfg_file[CA_FILENAME_PATH];
    int ret;

    if (conf->type == DB_POSTGRESQL) {
        /* check version */
        if (DBIntCheckVer(dbver) == 0) {
            LogPrintf(LV_INFO, "DB version: %s", dbver);
            if (strcmp(dbver, DB_VERSION) == 0) {
                return 0;
            }
                
            /* upgrade: adding tables*/
            sprintf(cfg_file, "%s/db/postgresql/upgrade_tbl_db.sql", root);
            sprintf(tmp, "%s/db/postgresql/", root);
            sprintf(cmd, "cd %s; sudo -u %s psql -f %s", tmp, POSTGRESQL_USER, cfg_file);
            ret = system(cmd);
            if (ret) {
                ret = 0;
            }

            /* adding column 1.0 -> 1.1 */
            if (strcmp(dbver, "1.0") == 0) {
                /* datasets:
                        depth VARCHAR( 200 ) DEFAULT "-"
                */
                /* items:
                        days INTEGER,
                        seconds INTEGER,
                        metadata VARCHAR( 255 ),
                */
                LogPrintf(LV_INFO, "Add coulumn deph to datasets table and coulumns days, seconds and metadata to items tables");
                if (DBIntTablesTo1_1() != 0) {
                    LogPrintf(LV_ERROR, "Tables upgrade to 1.1: failed");
                }
                /* records update */
                LogPrintf(LV_INFO, "Update items records");
                if (DBIntContentsTo1_1() != 0) {
                    LogPrintf(LV_ERROR, "Update items: failed");
                }
                
                LogPrintf(LV_INFO, "Update completed");
                DBIntSetVer("1.1");
            }
        }
    }

    return 0;
}


int main(int argc, char *argv[])
{
    char c;
    char config_file[CA_FILENAME_PATH];
    char root_dir[CA_FILENAME_PATH];
    char db_type[CA_FILENAME_PATH];
    char cert[CA_FILENAME_PATH];
    char hpath[CA_FILENAME_PATH];
    dbconf db_c;
    extern char *optarg;
    extern int optind, optopt;
    time_t twpcap;
    bool dbrun;
    short dbstep;
    uid_t uid;
    int ret;
    FILE *run;
    struct stat info;

    uid = getuid();
    pthread_mutex_init(&pd_mux, NULL);
    ds_tbl = NULL;
    dim = 0;
    md5en = TRUE;
    twpcap = CA_TIME_BETWEEN_PCAPS;
    config_file[0] = '\0';
    root_dir[0] = '\0';
    db_type[0] = '\0';
    cert[0] = '\0';
    hpath[0] = '\0';
    memset(&db_c, '\0', sizeof(dbconf));
    while ((c = getopt(argc, argv, "ivc:d:b:h")) != -1) {
        switch(c) {
        case 'v':
            printf("capanalysis %d.%d.%d\n", CAPANA_VER_MAG, CAPANA_VER_MIN, CAPANA_VER_REV);
            return 0;
            break;
        case 'c':
            sprintf(config_file, "%s", optarg);
            break;

        case 'd':
            sprintf(root_dir, "%s", optarg);
            break;

        case 'b':
            sprintf(db_type, "%s", optarg);
            break;

        case 'h':
            printf("capanalysis v%d.%d.%d\n", CAPANA_VER_MAG, CAPANA_VER_MIN, CAPANA_VER_REV);
            printf("%s\n", CAPANA_CR);
            Usage(argv[0]);
            return 0;
            break;

        case '?':
            LogPrintf(LV_ERROR, "Error: unrecognized option: -%c", optopt);
            Usage(argv[0]);
            exit(-1);
            break;
        }
    }
    
    LogCfg(config_file, root_dir);
    
    if (uid != 0) {
        LogPrintf(LV_FATAL, "Must be root to run it!");
        printf("Must be root to run it!\n");
        exit(-1);
    }
    
    printf("capanalysis v%d.%d.%d\n", CAPANA_VER_MAG, CAPANA_VER_MIN, CAPANA_VER_REV);
    printf("%s\n", CAPANA_CR);
    LogPrintf(LV_INFO, "capanalysis v%d.%d.%d", CAPANA_VER_MAG, CAPANA_VER_MIN, CAPANA_VER_REV);
    LogPrintf(LV_INFO, "Started");
    
    /* db type */
    if (config_file[0] == '\0') {
        if (strcmp(db_type, DB_T_POSTGRES) == 0) {
            db_c.type = DB_POSTGRESQL;
            /* default config file */
            sprintf(config_file, CA_DEFAULT_POSTGRES_CFG, root_dir);
        }
        else {
            Usage(argv[0]);
        
            return 0;
        }
    }
    if (config_file[0] != '\0') {
        /* read config file */
        if (ReadConfigFile(config_file, &db_c, root_dir, &twpcap, cert) != 0) {
            Usage(argv[0]);
            
            return 0;
        }
    }

    if (root_dir[0] != '\0') {
        /* daemon */
#if ROOT_USER
        ret = daemon(1, 0);
#endif

        /* pid */
        run = fopen(CA_CAPANA_RUN, "w+");
        if (run != NULL) {
            fprintf(run, "%i\n", getpid());
            fclose(run);
        }
        /* configure user interface installer */
        UInstall(root_dir);
        
        /* init db connection and/or creation */
        dbstep = 0;
        do {
            dbrun = TRUE;
            ret = DBIntInit(&db_c);
            if (ret != 0) {
                dbrun = FALSE;
                sprintf(hpath, CA_DB_STATUS, root_dir);
                /* DB installation and configuration */
                switch (ret) {
                case -1:
                    /* no connection */
                    run = fopen(hpath, "w+");
                    fprintf(run, "CON\n");
                    fclose(run);
                    break;
                    
                case -2:
                    /* user permision or existence */
                    run = fopen(hpath, "w+");
                    fprintf(run, "USR\n");
                    fclose(run);
                    dbstep = 0; /* the user is not ok */
                    break;
                    
                case -3:
                    /* DB permision or existence */
                    run = fopen(hpath, "w+");
                    fprintf(run, "DB\n");
                    fclose(run);
                    dbstep = 1; /* the user is ok but do not exist the DB */
                    break;
                }
            }
            if (dbrun == TRUE && DBIntCheckDB() != 0) {
                dbrun = FALSE;
                DBIntClose();
                sprintf(hpath, CA_DB_STATUS, root_dir);
                /* DB installation and configuration */
                run = fopen(hpath, "w+");
                fprintf(run, "DB\n");
                fclose(run);
                dbstep = 1; /* the user is ok */
            }
            if (dbrun == FALSE) {
                /* DB creation */
                sprintf(hpath, CA_DB_MAKE, root_dir);
                if (stat(hpath, &info) == 0) {
                    switch (dbstep) {
                    case 0: /* first step */
                        /* new user password */
                        DBCngUserPwd(&db_c, root_dir);
                        break;

                    case 1: /* create the DB */
                        DBCreate(&db_c, root_dir);
                        
                    default:
                        remove(hpath);
                        dbstep = 0;
                        break;
                    }
                }
                else {
                    if (terminate == TRUE) {
                        return 0;
                    }
                    sleep(1);
                }
            }
        } while (dbrun == FALSE);
        sprintf(hpath, CA_DB_MAKE, root_dir);
        if (stat(hpath, &info) == 0) {
            remove(hpath);
        }
        /* DB ok */
        sprintf(hpath, CA_DB_STATUS, root_dir);
        run = fopen(hpath, "w+");
        fprintf(run, "OK\n");
        fclose(run);
        
        /* upgrade DB */
        DBUpgrade(&db_c, root_dir);
        
        /* install user interface */
        PkgInstall(root_dir, "www");
        UIConfig(&db_c, root_dir);

        /* kill all xplico running */
        ret = system("killall xplico 2>/dev/null 1>/dev/null");

        /* sigterm function */
        terminate = FALSE;
        signal(SIGTERM, CapanaSigTerm);

        /* init session params */
        SeDeInit(cert, root_dir);

        CapanaLoop(&db_c, root_dir, twpcap);

        /* close db connection */
        DBIntClose();
    }
    else {
        Usage(argv[0]);
    }

    return 0;
}
