/* session_decoding.c
 * Session decoding monitoring
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


#define _GNU_SOURCE
#include <sys/types.h>
#include <dirent.h>
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <limits.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <signal.h>
#include <netinet/in.h>
#include <semaphore.h>
#include <pthread.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/select.h>
#include <sys/socket.h>
#include <net/if.h>
#include <sys/time.h>
#include <netdb.h>
#include <errno.h>

#include <openssl/ssl.h>
#include <openssl/err.h>

#include "session_decoding.h"
#include "dbinterface.h"
#include "config_file.h"
#include "log.h"

#define CA_MNP_STR_DIM     32
#define CA_PCAP_BUFFER     (1024*1024)

typedef struct {
    int ds;
    int sd4;
    int sd6;
    char main_dir[CA_MNP_STR_DIM];
    char cert[CA_MNP_STR_DIM];
} pcapip_thr;


static char *ssl_cert = NULL;
static char filename[CA_FILENAME_PATH];


static int ListSort(const void *a, const void *b)
{
    return strcmp(*(char **)a, *(char **)b);
}


static unsigned short SeDePort(unsigned short port)
{
    struct sockaddr_in servAddr;
    int yes, sd;
    
    /* create socket */
    sd = socket(AF_INET, SOCK_STREAM, 0);
    if (sd < 0) {
        LogPrintf(LV_WARNING, "Can't open socket (PCAPoverIP)");
        return 0;
    }
    yes = 1;
    if (setsockopt(sd, SOL_SOCKET, SO_REUSEADDR,
                   (char *) &yes, sizeof (yes)) < 0) {
        LogPrintf(LV_ERROR, "setsockopt");
        close(sd);
        return 0;
    }
#ifdef SO_REUSEPORT
    if (setsockopt(sd, SOL_SOCKET, SO_REUSEPORT,
                   (char *) &yes, sizeof(yes)) < 0) {
        perror("SO_REUSEPORT");
        close(sd);
        return 0;
    }
#endif

    do {
        /* bind server port */
        memset (&servAddr, 0, sizeof (servAddr));
        servAddr.sin_family = AF_INET;
        servAddr.sin_addr.s_addr = htonl(INADDR_ANY);
        servAddr.sin_port = htons(port);
        
        if (bind(sd, (struct sockaddr *) &servAddr, sizeof(servAddr)) == 0) {
            break;
        }
        port++;
    } while (port != 0);
    
    close(sd);
    
    return port;
}


static int *SeDePcapIP(const char *main_dir, int ds_id, int *sd)
{
    struct addrinfo hints, *servinfo, *add;
    char sport[25];
    int rv, yes, opts;
    int sd4, sd6;
    char pcapip_port[CA_FILENAME_PATH];
    unsigned short port;
    FILE *fp;

    port = CA_PCAP_IP_DEF_PROT + ds_id;
    port = SeDePort(port);

    memset(&hints, 0, sizeof hints);
    hints.ai_family = AF_UNSPEC;
    hints.ai_socktype = SOCK_STREAM;
    hints.ai_flags = AI_PASSIVE;        /* use my IP */
    hints.ai_protocol = IPPROTO_TCP;
    sprintf(sport, "%i", port);

    rv = getaddrinfo(NULL, sport, &hints, &servinfo);
    if (rv != 0) {
        LogPrintf(LV_ERROR, "getaddrinfo() failed, %s", gai_strerror(rv));
        return NULL;
    }
    sd4 = sd6 = 0;
    for (add = servinfo; add != NULL; add = add->ai_next) {
        if (add->ai_family == AF_INET) {
            if (sd4 == 0) {
                sd4 = socket(add->ai_family, add->ai_socktype, add->ai_protocol); 
                if (sd4 == -1) {
                    sd4 = 0;
                    continue;
                }
                
                yes = 1;
                if (setsockopt(sd4, SOL_SOCKET, SO_REUSEADDR, (char *) &yes, sizeof (yes)) < 0) {
                    LogPrintf(LV_ERROR, "setsockopt");
                    close(sd4);
                    return NULL;
                }
#ifdef SO_REUSEPORT
                if (setsockopt(sd4, SOL_SOCKET, SO_REUSEPORT, (char *) &yes, sizeof(yes)) < 0) {
                    perror("SO_REUSEPORT");
                    close(sd4);
                    LogPrintf(LV_ERROR, "SO_REUSEPORT");
                    return NULL;
                }
#endif
                opts = fcntl(sd4, F_GETFL);
                if (opts < 0) {
                    perror("fcntl(F_GETFL) failed");
                    close(sd4);
                    LogPrintf(LV_ERROR, "fcntl(F_GETFL) failed");
                    return NULL;
                }
                opts = opts | O_NONBLOCK;
                if (fcntl(sd4, F_SETFL, opts) < 0) {
                    perror("fcntl(F_SETFL) failed");
                    close(sd4);
                    LogPrintf(LV_ERROR, "fcntl(F_SETFL) failed");
                    return NULL;
                }
                rv = bind(sd4, add->ai_addr, add->ai_addrlen);
                if (rv == -1) {
                    close(sd4);
                    LogPrintf(LV_ERROR, "Cannot bind port");
                    return NULL;
                }
            }
        }
        else if (add->ai_family == AF_INET6) {
            if (sd6 == 0) {
                sd6 = socket(add->ai_family, add->ai_socktype, add->ai_protocol); 
        		if (sd6 == -1) {
                    sd6 = 0;
                    continue;
                }
                
                yes = 1;
                if (setsockopt(sd6, SOL_SOCKET, SO_REUSEADDR, (char *) &yes, sizeof (yes)) < 0) {
                    LogPrintf(LV_ERROR, "setsockopt");
                    close(sd6);
                    return NULL;
                }
#ifdef SO_REUSEPORT
                if (setsockopt(sd6, SOL_SOCKET, SO_REUSEPORT, (char *) &yes, sizeof(yes)) < 0) {
                    perror("SO_REUSEPORT");
                    close(sd6);
                    LogPrintf(LV_ERROR, "SO_REUSEPORT");
                    return NULL;
                }
#endif
                if (setsockopt(sd6, IPPROTO_IPV6, IPV6_V6ONLY, &yes, sizeof(yes)) < 0) {
                    perror("IPV6_V6ONLY");
                    close(sd6);
                    LogPrintf(LV_ERROR, "IPV6_V6ONLY");
                    return NULL;
                }
                
                opts = fcntl(sd6, F_GETFL);
                if (opts < 0) {
                    perror("fcntl(F_GETFL) failed");
                    close(sd6);
                    LogPrintf(LV_ERROR, "fcntl(F_GETFL) failed");
                    return NULL;
                }
                opts = opts | O_NONBLOCK;
                if (fcntl(sd6, F_SETFL, opts) < 0) {
                    perror("fcntl(F_SETFL) failed");
                    close(sd6);
                    LogPrintf(LV_ERROR, "fcntl(F_SETFL) failed");
                    return NULL;
                }
                rv = bind(sd6, add->ai_addr, add->ai_addrlen);
                if (rv == -1) {
                    close(sd6);
                    LogPrintf(LV_ERROR, "Cannot bind port");
                    return NULL;
                }
            }
        }
    }

    if (sd4 == 0 && sd6 == 0) {
        LogPrintf(LV_ERROR, "Unable to bind to either IPv4 or IPv6 address");
        return NULL;
    }

    if (sd4 != 0) {
        listen(sd4, 1);  /* only one connection */ 
    }
    if (sd6 != 0) {
        listen(sd6, 1);  /* only one connection */ 
    }

    sd[0] = sd4;
    sd[1] = sd6;

    /* info used by the XI */
    sprintf(pcapip_port, CA_TMP_DIR"/"CA_PCAPIP_FILE_PORT, main_dir, ds_id);
    fp = fopen(pcapip_port, "w+");
    if (fp != NULL) {
        fprintf(fp, "%i\n", port);
        fclose(fp);
    }
    LogPrintf(LV_INFO, "PCAPoIP: port %i", port);

    return sd;
}


static void ShowCerts(SSL *ssl)
{   X509 *cert;
    char *line;

    cert = SSL_get_peer_certificate(ssl);	/* Get certificates (if available) */
    if (cert != NULL) {
        LogPrintf(LV_DEBUG, "Server certificates:");
        line = X509_NAME_oneline(X509_get_subject_name(cert), 0, 0);
        LogPrintf(LV_DEBUG, "Subject: %s", line);
        free(line);
        line = X509_NAME_oneline(X509_get_issuer_name(cert), 0, 0);
        LogPrintf(LV_DEBUG, "Issuer: %s", line);
        free(line);
        X509_free(cert);
    }
    else
        LogPrintf(LV_WARNING, "No certificates.");
}


static void *SeDePcapThread(void *data)
{
    int rv, ds_id, opts, rd, wr, wr_tmp;
    int sd4, sd6, maxsd, cnt;
    fd_set sd_set, cp_set;
    struct sockaddr_storage their_addr;
    socklen_t sin_size;
    int sd, sock;
    pcapip_thr *thr_info;
    char file_pcap[CA_FILENAME_PATH];
    char dec_pcap[CA_FILENAME_PATH];
    char main_dir[CA_FILENAME_PATH];
    char *datacap;
    bool compl;
    FILE *fp;
    time_t tpcap;
    SSL_CTX *ctx;
    const SSL_METHOD *method;
    SSL *ssl;
    
    thr_info = (pcapip_thr *)data;
    sd4 = thr_info->sd4;
    sd6 = thr_info->sd6;
    ds_id = thr_info->ds;
    strcpy(main_dir, thr_info->main_dir);
    thr_info = NULL;
    cnt = 0;
    free(data);
    
    /* setupt ssl */
    ctx = NULL;
    ssl = NULL;
    if (ssl_cert != NULL) {
        /* create new server-method instance */
        method = SSLv23_server_method();
        if (method == NULL) {
            ERR_print_errors_fp(stderr);
            abort();
        }
        /* create new context from method */
        ctx = SSL_CTX_new(method);
        if (ctx != NULL) {
            /* set the local certificate from CertFile */
            if (SSL_CTX_use_certificate_file(ctx, ssl_cert, SSL_FILETYPE_PEM) <= 0) {
                ERR_print_errors_fp(stderr);
                SSL_CTX_free(ctx);
                ctx = NULL;
            }
            /* set the private key from KeyFile (may be the same as CertFile) */
            if (ctx != NULL && SSL_CTX_use_PrivateKey_file(ctx, ssl_cert, SSL_FILETYPE_PEM) <= 0) {
                ERR_print_errors_fp(stderr);
                SSL_CTX_free(ctx);
                ctx = NULL;
            }
            /* verify private key */
            if (ctx != NULL && !SSL_CTX_check_private_key(ctx)) {
                ERR_print_errors_fp(stderr);
                SSL_CTX_free(ctx);
                ctx = NULL;
            }
        }
        else {
            ERR_print_errors_fp(stderr);
        }
    }
    
    /* wait first connection */
    FD_ZERO(&sd_set);
    if (sd4 != 0)
        FD_SET(sd4, &sd_set);
    if (sd6 != 0)
        FD_SET(sd6, &sd_set);
    maxsd = sd4;
    if (sd6 > maxsd)
        maxsd = sd6;
    while (1) {
        memcpy(&cp_set, &sd_set, sizeof(sd_set));
        rv = select(maxsd + 1, &cp_set, NULL, NULL, NULL);
        if (rv < 0) {
            if (errno == EINTR)
                continue;
            
            if (sd4 != 0)
                close(sd4);
            if (sd6 != 0)
                close(sd6);
            
            return NULL;
        }

        if (sd4 != 0 && FD_ISSET(sd4, &cp_set)) {
            sin_size = sizeof(their_addr);
            sock = accept(sd4, (struct sockaddr *)&their_addr, &sin_size);
            if (sock == -1)
                continue;
            opts = fcntl(sd4, F_GETFL);
            if (opts < 0) {
                perror("fcntl(F_GETFL) failed");
                continue;
            }
            opts = (opts & (~O_NONBLOCK));
            if (fcntl(sd4, F_SETFL, opts) < 0) {
                perror("fcntl(F_SETFL) failed");
                continue;
            }
            
            sd = sd4;
            close(sd6);
            break;
        }
        if (sd6 != 0 && FD_ISSET(sd6, &cp_set)) {
            sin_size = sizeof(their_addr);
            sock = accept(sd6, (struct sockaddr *)&their_addr, &sin_size);
            if (sock == -1)
                continue;
            opts = fcntl(sd6, F_GETFL);
            if (opts < 0) {
                perror("fcntl(F_GETFL) failed");
                continue;
            }
            opts = (opts & (~O_NONBLOCK));
            if (fcntl(sd6, F_SETFL, opts) < 0) {
                perror("fcntl(F_SETFL) failed");
                continue;
            }
            
            sd = sd6;
            close(sd4);
            break;
        }
    }

    while (1) {
        if (sock == -1) {
            sock = accept(sd, (struct sockaddr *)&their_addr, &sin_size);
            if (sock == -1)
                if (errno != EAGAIN)
                    return NULL;
                continue;
        }
        if (ctx != NULL) {
            /* get new SSL state with context */
            ssl = SSL_new(ctx);
            /* set connection socket to SSL state */
            SSL_set_fd(ssl, sock);
            /* SSL-protocol accept */
            if (SSL_accept(ssl) != 1) {
                ERR_print_errors_fp(stderr);
            }
            else {
                ShowCerts(ssl);
            }
        }

        /* write file */
        compl = TRUE;
        tpcap = time(NULL);
        sprintf(file_pcap, CA_TMP_DIR"/%lu_%i_%i.pcap", main_dir, ds_id, tpcap, ds_id, cnt);
        fp = fopen(file_pcap, "w");
        if (fp != NULL) {
            datacap = malloc(CA_PCAP_BUFFER);
            if (datacap != NULL) {
                do {
                    if (ctx != NULL) {
                        rd = SSL_read(ssl, datacap, CA_PCAP_BUFFER);
                    }
                    else {
                        rd = read(sock, datacap, CA_PCAP_BUFFER);
                    }
                    if (rd > 0) {
                        wr = 0;
                        do {
                            wr_tmp = fwrite(datacap+wr, 1, rd-wr, fp);
                            if (wr_tmp == -1) {
                                if (errno != EINTR) {
                                    compl = FALSE;
                                    break;
                                }
                            }
                            else {
                                wr += wr_tmp;
                            }
                        } while (wr != rd);
                    }
                    else if (rd == -1 && errno != EINTR) {
                        compl = FALSE;
                        break;
                    }
                } while (rd != 0);
                free(datacap);
            }
            else {
                compl = FALSE;
            }
            fclose(fp);
            if (ssl != NULL) {
                SSL_free(ssl);
                ssl = NULL;
            }
            close(sock);
            sock = -1;
        }
        else {
            if (ssl != NULL) {
                SSL_free(ssl);
                ssl = NULL;
            }
            compl = FALSE;
            close(sock);
            sock = -1;
        }

        /* decode pcap */
        if (compl == TRUE) {
            sprintf(dec_pcap, CA_NEW_DIR"/%lu_%i_%i.pcap", main_dir, ds_id, tpcap, ds_id, cnt++);
            rename(file_pcap, dec_pcap);
            LogPrintf(LV_INFO, "New pcap: %s", dec_pcap);
        }
    }
    
    close(sd);
    if (ctx != NULL)
        SSL_CTX_free(ctx);
    return NULL;
}


static bool SeDeDataSExist(char *root_dir, int id)
{
    struct stat info;
    char ndir[CA_FILENAME_PATH];
    
    sprintf(ndir, CA_NEW_DIR, root_dir, id);  
    if (stat(ndir, &info) != 0)
        return FALSE;
    
    return TRUE;
}


static int SeDeCreateDirs(char *root_dir, int id)
{
    struct stat info;
    char ndir[CA_FILENAME_PATH];
    int ret;
    
    sprintf(ndir, CA_DS_DIR, root_dir, id);
    stat(ndir, &info);
    
    sprintf(ndir, CA_LOG_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_NEW_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_RAW_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_DECOD_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_DATA_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_FAULT_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_TMP_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_FILTERS_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    sprintf(ndir, CA_HISTORY_DIR, root_dir, id);
    mkdir(ndir, 0x01FF);
    ret = chown(ndir, info.st_uid, info.st_gid);
    
    return 0;
}


int SeDeInit(char *cert, char *root_dir)
{
    struct stat info;
    
    if (cert[0] == '\0') {
        /* default file path */
        sprintf(cert, CA_DEFAULT_CAPANA_CERT, root_dir);
    }
    if (stat(cert, &info) != 0) {
        ssl_cert = NULL;
        return 0;
    }
    
    ssl_cert = malloc(strlen(cert)+1);
    strcpy(ssl_cert, cert);

    /* load & register all cryptos, etc. */
    SSL_library_init();
    OpenSSL_add_all_algorithms();
    /* load all error messages */
    SSL_load_error_strings();

    return 0;
}


int SeDeFind(char *main_dir, dsdec *tbl, int dim)
{
    DIR *dir;
    struct dirent *entry;
    int i, ds_id;
    int dnew, next, pre;
    int len_ds, ret;
    char dir_path[CA_FILENAME_PATH];
    char pcapip_port[CA_FILENAME_PATH];
    pthread_t pid;
    pcapip_thr *thr_info;
    
    dir = opendir(main_dir);
    if (dir == NULL) {
        perror("");
        exit(-1);
        return -1;
    }

    /* first empty position in tbl */
    next = 0;
    while (tbl[next].ds_id != -1)
        next++;

    /* ds directory */
    dnew = 0;
    len_ds = strlen(CA_DS_NAME);
    while((entry = readdir(dir)) != NULL && dnew < CA_TBL_ADD) {
        if (entry->d_name[0] == '.')
            continue;
        /* check if this directory is a ds directory and if already exist in tbl */
        if (strncmp(entry->d_name, CA_DS_NAME, len_ds) == 0) {
            if (sscanf(entry->d_name, CA_DS_NAME"%i", &ds_id) == 1) {
                for (i=0; i<dim; i++) {
                    if (tbl[i].ds_id == ds_id) {
                        break;
                    }
                }
                /* next free position */
                while (tbl[next].ds_id != -1)
                    next++;
                if (i == dim) {
                    sprintf(dir_path, CA_DS_DIR, main_dir, ds_id);
                    if (SeDeDataSExist(main_dir, ds_id) == FALSE) { /* new ds */
                        LogPrintf(LV_INFO, "New DataSet dir: %s", dir_path);
                        DBIntDbTable(main_dir, ds_id);
                        SeDeCreateDirs(main_dir, ds_id);
                    }
                    else
                        LogPrintf(LV_INFO, "DataSet dir: %s", dir_path);
                    tbl[next].ds_id = ds_id;
                    tbl[next].run = FALSE;
                    memset(&tbl[next].pid, 0, sizeof(task));
                    tbl[next].end = FALSE;
                    tbl[next].name[0] = '\0';
                    tbl[next].size = 0;
                    tbl[next].filenum = 0;
                    if (DBIntDeep(ds_id, &tbl[next].deep) != 0) {
                        tbl[next].deep.tp = CA_DP_NONE;
                    }
                    dnew++;
                }
            }
        }
    }
    closedir(dir);

    pre = 0;
    if (next > CA_PCAP_MAX) {
        /* close old connection */
        pre = next - CA_PCAP_MAX;
        for (i=0; i!=pre; i++) {
            if (tbl[i].sd[0] != -1) {
                if (tbl[i].sd[0] != 0)
                    close(tbl[i].sd[0]);
                tbl[i].sd[0] = -1;
            }
            if (tbl[i].sd[1] != -1) {
                if (tbl[i].sd[1] != 0)
                    close(tbl[i].sd[1]);
                tbl[i].sd[1] = -1;
            }
            sprintf(pcapip_port, CA_TMP_DIR"/"CA_PCAPIP_FILE_PORT, main_dir, tbl[i].ds_id);
            remove(pcapip_port);
        }
    }
    for (i=pre; i!=next; i++) {
        if (tbl[i].sd[0] == -1 && tbl[i].sd[1] == -1) {
            /* start thread */
            if (SeDePcapIP(main_dir, tbl[i].ds_id, tbl[i].sd) != NULL) {
                thr_info = malloc(sizeof(pcapip_thr));
                thr_info->sd4 = tbl[i].sd[0];
                thr_info->sd6 = tbl[i].sd[1];
                thr_info->ds = tbl[i].ds_id;
                strcpy(thr_info->main_dir, main_dir);
                ret = pthread_create(&pid, NULL, SeDePcapThread, (void *)thr_info);
                if (ret == 0) {
                    pthread_detach(pid);
                }
                else {
                    free(thr_info);
                    if (tbl[i].sd[0] != 0 && tbl[i].sd[0] != -1)
                        close(tbl[i].sd[0]);
                    if (tbl[i].sd[1] != 0 && tbl[i].sd[1] != -1)
                        close(tbl[i].sd[1]);
                    tbl[i].sd[0] = -1;
                    tbl[i].sd[1] = -1;
                }
            }
        }
    }

    return dnew;
}


int SeDeStart(dbconf *db_c, char *main_dir, int ds, task *pid, bool rt, char *interf, char *filter)
{
    int ptsk, ret;
    char app_path[CA_FILENAME_PATH];
    char config_file[CA_FILENAME_PATH];
    char work_dir[CA_FILENAME_PATH];
    char ds_n[CA_FILENAME_PATH];
    char end_file[CA_FILENAME_PATH];
    char cmd[2*CA_FILENAME_PATH];
    char *xpl_cfg;
    FILE *fxcfg;

    /* remove any other command file */
    sprintf(end_file, CA_DECOD_DIR"/%s", main_dir, ds, CA_END_FILE);
    remove(end_file);
    memset(pid, 0, sizeof(task));
    pid->tot = 0;
    
    /* cfg master files */
    switch (db_c->type) {
    case DB_POSTGRESQL:
        xpl_cfg = CA_XPLICO_POSTGRE_CFG;
        break;

    default:
        return -1;
    }
    
    /* start xplico */
    ptsk = fork();
    if (ptsk == 0) {
        /* remove old file */
        sprintf(config_file, CA_TMP_DIR"/%s", main_dir, ds, xpl_cfg);
        remove(config_file);

        /* create config file */
        sprintf(config_file, "%s/cfg/%s", main_dir, xpl_cfg);
        sprintf(work_dir, CA_TMP_DIR, main_dir, ds);
        sprintf(cmd, "cp -a %s %s", config_file, work_dir);
        ret = system(cmd);
        
        /* log, ds */
        sprintf(config_file, CA_TMP_DIR"/%s", main_dir, ds, xpl_cfg);
        fxcfg = fopen(config_file, "a");
        fprintf(fxcfg, "LOG_DIR_PATH="CA_LOG_DIR"\n", main_dir, ds);
        fprintf(fxcfg, "TMP_DIR_PATH="CA_TMP_DIR"/xplico\n", main_dir, ds);
        fprintf(fxcfg, "DISPATCH_DECODED_DIR="CA_DS_DIR"\n", main_dir, ds);

        /* DB connection params */
        switch (db_c->type) {
        case DB_POSTGRESQL:
            fprintf(fxcfg, CFG_PAR_DB_HOST"=%s\n", db_c->host);
            fprintf(fxcfg, CFG_PAR_DB_NAME"=%s\n", db_c->name);
            fprintf(fxcfg, CFG_PAR_DB_USER"=%s\n", db_c->user);
            fprintf(fxcfg, CFG_PAR_DB_PASSWORD"=%s\n", db_c->password);
            break;
        }
        fclose(fxcfg);

        /* xplico process */
        sprintf(app_path, "%s/bin/xplico", main_dir);
        sprintf(work_dir, CA_DECOD_DIR, main_dir, ds);
        sprintf(ds_n, "%i", ds);
        if (rt == FALSE) {
            execlp(app_path, "xplico", "-c", config_file, "-m", "ca", "-n", ds_n, "-d", work_dir, NULL);
        }
        else {
            execlp(app_path, "xplico", "-c", config_file, "-m", "rltm_ca", "-n", ds_n, "-r", "-i", interf, "-d", work_dir, NULL);
        }
        exit(-1);
    }
    else if (ptsk == -1) {
        return -1;
    }
    pid->xplico = ptsk;
    pid->tot++;

    return 0;
}


int SeDeEnd(char *main_dir, int ds, task *pid)
{
    char end_file[CA_FILENAME_PATH];
    int fd;

    /* end xplico */
    sprintf(end_file, CA_DECOD_DIR"/%s", main_dir, ds, CA_END_FILE);
    fd = open(end_file, O_CREAT|O_RDWR, 0x01B6); /* only create file */
    if (fd != -1)
        close(fd);

    return 0;
}


static char *SeDeFileSrc(char *main_dir, int ds, short type, bool *one)
{
    char newdir[CA_FILENAME_PATH];
    DIR *dir;
    struct dirent *entry;
    int i, num;
    char **list;
    
    switch (type) {
    case 0:
        sprintf(newdir, CA_NEW_DIR, main_dir, ds);
        break;

    case 1:
        sprintf(newdir, CA_DECOD_DIR, main_dir, ds);
        break;
    }
    
    dir = opendir(newdir);
    if (dir == NULL) {
        return NULL;
    }

    /* file list */
    num = 0;
    list = NULL;
    while((entry = readdir(dir)) != NULL) {
        if (entry->d_name[0] == '.')
            continue;
        list = realloc(list, sizeof(char *)*(num+1));
        list[num] = malloc(strlen(entry->d_name)+5);
        strcpy(list[num], entry->d_name);
        num++;
    }
    closedir(dir);
    if (one != NULL) {
        if (num > 1)
            *one = FALSE;
        else
            *one = TRUE;
    }
    
    /* sort */
    qsort(list, num, sizeof(char *), ListSort);
    if (num == 0) {
        return NULL;
    }
    if (list != NULL) {
        strcpy(filename, list[0]);
        for (i=0; i<num; i++) {
            free(list[i]);
        }
        free(list);
    }

    return filename;
}


char *SeDeFileNew(char *main_dir, int ds, bool *one)
{
    return SeDeFileSrc(main_dir, ds, 0, one);
}


char *SeDeFileDecode(char *main_dir, int ds)
{
    return SeDeFileSrc(main_dir, ds, 1, NULL);
}


bool SeDeFileActive(char *filepath)
{
    int fd;
    bool ret;

    ret = FALSE;
    fd = open(filepath, O_RDONLY);
    if (fd < 0) {
        perror("open");
        return FALSE;
    }

    if (fcntl(fd, F_SETLEASE, F_WRLCK) && EAGAIN == errno) {
        ret = TRUE;
    }
    else {
        fcntl(fd, F_SETLEASE, F_UNLCK);
    }
    close(fd);
    
    return ret;

}


int SeDeRun(task *pid, pid_t chld, bool tclear)
{
    int ret;
    
    ret = -1;

    /* check all application */
    if (pid->xplico == chld) {
        if (tclear)
            pid->xplico = 0;
        ret = 0;
    }

    return ret;
}


int SeDeKill(dsdec *tbl, int id)
{
    if (tbl[id].run == TRUE) {
        /* decoder */
        if (tbl[id].pid.xplico != 0)
            kill(tbl[id].pid.xplico, SIGKILL);
    }
    
    return 0;
}

