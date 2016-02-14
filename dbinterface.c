/* dbinterface.c
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
 
#include <arpa/inet.h>
#include <stdlib.h>
#include <stdio.h>
#include <time.h>
#include <string.h>
#include <sqlite3.h>
#include <postgresql/libpq-fe.h>

#include "dbinterface.h"
#include "log.h"


/* postgres, sqlite */
static PGconn *psql;               /* Postgres DB */
static dbtype dbt;                 /* db type */
static dbconf bconf;               /* a copy of native configuration */


static int DBIntQuery(char *query, unsigned long *id)
{
    int ret;
    short try = 1;
    PGresult *res;

    ret = -1;
    if (dbt == DB_POSTGRESQL) {
        do {
            res = PQexec(psql, query);
            if (PQresultStatus(res) != PGRES_COMMAND_OK && PQresultStatus(res) != PGRES_TUPLES_OK) {
                LogPrintf(LV_ERROR, "PQexec: %s", PQresultErrorMessage(res));
                PQclear(res);
                DBIntClose();
                DBIntInit(&bconf);
            }
            else {
                ret = 0;
                break;
            }
        } while(try--);
        if (ret == 0 && id != NULL) {
            *id = atol(PQgetvalue(res, 0, 0));
        }
        if (ret == 0) {
            PQclear(res);
        }
    }
    #warning "to complete"

    return ret;
}


static int DBIntQueryStr(char *query, char *str, int len)
{
    int ret;
    short try = 1;
    PGresult *res;
    char *val;
    
    ret = -1;
    if (dbt == DB_POSTGRESQL) {
        do {
            res = PQexec(psql, query);
            if (PQresultStatus(res) != PGRES_COMMAND_OK && PQresultStatus(res) != PGRES_TUPLES_OK) {
                LogPrintf(LV_ERROR, "PQexec: %s", PQresultErrorMessage(res));
                PQclear(res);
                DBIntClose();
                DBIntInit(&bconf);
            }
            else {
                ret = 0;
                break;
            }
        } while(try--);
        if (ret == 0 && str != NULL) {
            val = PQgetvalue(res, 0, 0);
            if (val != NULL) {
                if (strlen(strncpy(str, val, len)) == len) {
                    LogPrintf(LV_ERROR, "Depth mal formed: %s", PQgetvalue(res, 0, 0));
                    str[0] = '\0';
                }
            }
            else {
                str[0] = '\0';
            }
        }
        if (ret == 0) {
            PQclear(res);
        }
    }
    #warning "to complete"

    return ret;
}


int DBIntInit(dbconf *conf)
{
    int res;
    char con_param[DBINT_QUERY_DIM];

    dbt = conf->type;

    if (dbt == DB_POSTGRESQL) {
        /* postgresql */
        memcpy(&bconf, conf, sizeof(dbconf));
        sprintf(con_param, "host = '%s' dbname = '%s' user = '%s' password = '%s' connect_timeout = '900'", bconf.host, bconf.name, bconf.user, bconf.password);
        psql = PQconnectdb(con_param);
        if (!psql) {
            return -1; /* db not running */
        }
        if (PQstatus(psql) != CONNECTION_OK) {
            sprintf(con_param, "\"%s\"", bconf.user);
            if (strstr(PQerrorMessage(psql), con_param)) {
                res = -2; /* user error */
                LogPrintf(LV_ERROR, "User fail: %s", PQerrorMessage(psql));
            }
            else {
                sprintf(con_param, "\"%s\"", bconf.name);
                if (strstr(PQerrorMessage(psql), con_param)) {
                    res = -3; /* user error */
                    LogPrintf(LV_ERROR, "DB name fail: %s", PQerrorMessage(psql));
                }
                else {
                    res = -1; /* db not running */
                    LogPrintf(LV_ERROR, "DB fail");
                }
            }
            PQfinish(psql);
            
            return res;
        }
    }
    else {
        return -1;
    }

    /* fix status on DB */
    switch (dbt) {
    case DB_POSTGRESQL:
        break;
    
    default:
        return -1;
        break;
    }

    return 0;
}


int DBIntClose(void)
{
    switch (dbt) {
    case DB_POSTGRESQL:
        PQfinish(psql);
        break;
    
    default:
        return -1;
        break;
    }

    return 0;
}


int DBIntFilePcap(int ds_id, size_t size, const char *name, const char *md5, const char *sha1, unsigned long *id)
{
    char query[DBINT_QUERY_DIM];

    if (*id == 0) {
        switch (dbt) {
        case DB_POSTGRESQL:
            sprintf(query, DBINT_1_QUERY_FILE, ds_id, size, name, "---", "---");
            if (DBIntQuery(query, id) != 0) {
                return -1;
            }
            break;
            
        default:
            return -1;
            break;
        }
    }
    else {
        switch (dbt) {            
        case DB_POSTGRESQL:
            sprintf(query, DBINT_1_QUERY_UPDATE_FILE, md5, sha1, *id);
            if (DBIntQuery(query, NULL) != 0) {
                return -1;
            }
            break;
            
        default:
            return -1;
            break;
        }
        
    }

    return 0;
}


int DBIntDeep(int ds_id, ds_depth *deep)
{
    int ret;
    char deep_str[DBINT_QUERY_DIM];
    char query[DBINT_QUERY_DIM];
    struct tm tmd;

    memset(&tmd, 0, sizeof(tmd));
    deep_str[0] = '\0';
    deep->tp = CA_DP_NONE;
    sprintf(query, DBINT_QUERY_DS_DEPTH, ds_id);
    ret = DBIntQueryStr(query, deep_str, sizeof(deep_str));
    if (ret == 0 && deep_str[0] != '\0') {
        if (strncmp("EOL:", deep_str, 4) == 0) {
            if (sscanf(deep_str, "EOL:%d-%d-%d", &tmd.tm_year, &tmd.tm_mon, &tmd.tm_mday) == 3) {
                tmd.tm_year -= 1900;
                tmd.tm_mon -= 1;
                tmd.tm_hour = 23;
                tmd.tm_min = 59;
                tmd.tm_sec = 59;
                deep->el = mktime(&tmd);
                deep->tp = CA_DP_EL;
                LogPrintf(LV_INFO, "EOL unix time: %d", deep->el);
            }
            else {
                LogPrintf(LV_ERROR, "Depth date malformed: %s", deep_str);
                ret = -1;
            }
        }
        else if (strncmp("TD:", deep_str, 3) == 0) {
        }
        else if (strncmp("FD:", deep_str, 3) == 0) {
        }
        else if (strncmp("SZ:", deep_str, 3) == 0) {
        }
        else {
            ret = -1;
        }
    }
    else
        ret = -1;
    
    return ret;
}


int DBIntDeleteDataSet(int ds_id)
{
    char query[DBINT_QUERY_DIM];
    
    switch (dbt) {        
    default:
        /* not return -1 */
        break;
    }
    
    /* query delete dataset and items table */
    sprintf(query, DBINT_QUERY_DROP_TABLE, DBINI_ITEMS_STR, ds_id);
    DBIntQuery(query, NULL);
    
    sprintf(query, DBINT_QUERY_DROP_TABLE, DBINI_IPS_STR, ds_id);
    DBIntQuery(query, NULL);

    sprintf(query, DBINT_QUERY_DELETE_DS, ds_id);
    if (DBIntQuery(query, NULL) != 0) {
        return -1;
    }
    
    return 0;
}


int DBIntDbTable(char *root_dir, int ds_id)
{
    char buff[DBCFG_BUFF_SIZE];
    char mod[DBCFG_BUFF_SIZE];
    char file[DBINT_QUERY_DIM*10];
    char *p;
    FILE *fp;

    /* items */
    switch (dbt) {
    case DB_POSTGRESQL:
        sprintf(buff, DBINI_POSTGRES_ITEMS_SQL, root_dir);
        break;
    }
    
    fp = fopen(buff, "r");
    if (fp) {
        file[0] = '\0';
        while (fgets(buff, DBCFG_BUFF_SIZE, fp) != NULL) {
            if (buff[0] == '-')
                continue;
            p = strstr(buff, "dID_items");
            if (p != NULL) {
                p[0] = '\0';
                p += 9;
                sprintf(mod, "%s%s%i%s", buff, DBINI_ITEMS_STR, ds_id, p);
                strcat(file, mod);
            }
            else
                strcat(file, buff);
        }
        fclose(fp);
    
        DBIntQuery(file, NULL);
    }

    /* ips */
    switch (dbt) {
    case DB_POSTGRESQL:
        sprintf(buff, DBINI_POSTGRES_IPS_SQL, root_dir);
        break;
    }
    
    fp = fopen(buff, "r");
    if (fp) {
        file[0] = '\0';
        while (fgets(buff, DBCFG_BUFF_SIZE, fp) != NULL) {
            if (buff[0] == '-')
                continue;
            p = strstr(buff, "dID_ips");
            if (p != NULL) {
                p[0] = '\0';
                p += 7;
                sprintf(mod, "%s%s%i%s", buff, DBINI_IPS_STR, ds_id, p);
                strcat(file, mod);
            }
            else
                strcat(file, buff);
        }
        fclose(fp);
    
        DBIntQuery(file, NULL);
    }
    
    return 0;
}


int DBIntCheckDB(void)
{
    int ret;
    unsigned long cnt;
    char query[DBINT_QUERY_DIM];
    
    /* query count dataset and items table */
    sprintf(query, DBINT_QUERY_CHECK);
    ret = DBIntQuery(query, &cnt);
    if (ret == 0 && cnt != 1) {
        //printf("Check DB: %i\n", cnt);
        ret = -1;
    }
    
    return ret;
}


int DBIntCheckVer(char *ver)
{
    int ret;
    char query[DBINT_QUERY_DIM];
    short try = 1;
    PGresult *res;

    ret = -1;
    ver[0] = '\0';
    
    /* query count dataset and items table */
    sprintf(ver, "1.0");
    sprintf(query, DBINT_QUERY_VERSION);
    if (dbt == DB_POSTGRESQL) {
        do {
            res = PQexec(psql, query);
            if (PQresultStatus(res) != PGRES_COMMAND_OK && PQresultStatus(res) != PGRES_TUPLES_OK) {
                if (PQresultStatus(res) == PGRES_FATAL_ERROR) {
                    PQclear(res);
                    return 0;
                }
                LogPrintf(LV_ERROR, "PQexec: %s", PQerrorMessage(psql));
                LogPrintf(LV_ERROR, "%i", PQresultStatus(res));
                PQclear(res);
                DBIntClose();
                DBIntInit(&bconf);
            }
            else {
                ret = 0;
                break;
            }
        } while(try--);
        if (ret == 0) {
            if (PQntuples(res) == 1) {
                strcpy(ver, PQgetvalue(res, 0, 0));
            }
            else {
                LogPrintf(LV_WARNING, "Version DB not found");
            }
            PQclear(res);
        }
    }
    
    return ret;
}


int DBIntSetVer(char *ver)
{
    char query[DBINT_QUERY_DIM];
    
    sprintf(query, DBINT_QUERY_SET_VERSION, ver);
    DBIntQuery(query, NULL);
    
    return 0;
}


int DBIntTablesTo1_1(void) {
    int ret, i, id, item_id;
    char query[DBINT_QUERY_DIM];
    PGresult *res;
    
    sprintf(query, DBINI_POSTGRES_UPG_DS_1);
    ret = DBIntQuery(query, NULL);
    sprintf(query, DBINI_POSTGRES_UPG_DS_2);
    ret = DBIntQuery(query, NULL);
    
    res = PQexec(psql, DBINI_POSTGRES_UPG_DATASETS);
    if (PQresultStatus(res) == PGRES_TUPLES_OK) {
        item_id = PQfnumber(res, "id");
        for (i=0; i<PQntuples(res); i++) {
            id = atoi(PQgetvalue(res, i, item_id));
            sprintf(query, DBINI_POSTGRES_UPG_ITM_2, id);
            ret = DBIntQuery(query, NULL);
            
            sprintf(query, DBINI_POSTGRES_UPG_ITM_3, id);
            ret = DBIntQuery(query, NULL);
            
            sprintf(query, DBINI_POSTGRES_UPG_ITM_4, id);
            ret = DBIntQuery(query, NULL);
        }
    }
    PQclear(res);

    return ret;
}


int DBIntContentsTo1_1(void)
{
    int i, j, id, ds_id, item_id, ctime_id, cdate_id;
    char query[DBINT_QUERY_DIM];
    char *ctime, *cdate, *iid;
    PGresult *res_ds, *res_items;
    unsigned long days, sec;
    time_t time_or;
    
    res_ds = PQexec(psql, DBINI_POSTGRES_UPG_DATASETS);
    if (PQresultStatus(res_ds) == PGRES_TUPLES_OK) {
        ds_id =  PQfnumber(res_ds, "id");
        for (i=0; i<PQntuples(res_ds); i++) {
            id = atoi(PQgetvalue(res_ds, i, ds_id));
            sprintf(query, DBINI_POSTGRES_UPG_ITEMS, id);
            res_items = PQexec(psql, query);
            if (PQresultStatus(res_items) == PGRES_TUPLES_OK) {
                item_id = PQfnumber(res_items, "id");
                cdate_id = PQfnumber(res_items, "depo");
                ctime_id = PQfnumber(res_items, "tepo");
                for (j=0; j<PQntuples(res_items); j++) {
                    iid = PQgetvalue(res_items, j, item_id);
                    cdate = PQgetvalue(res_items, j, cdate_id);
                    ctime = PQgetvalue(res_items, j, ctime_id);
                    time_or = atol(cdate);
                    sec = atol(ctime);
                    days = time_or/86400; /* (3600*24) */
                    sprintf(query, DBINI_POSTGRES_UPG_ITEM_DAYS, id, days, sec, iid);
                    DBIntQuery(query, NULL);
                }
            }
            PQclear(res_items);
        }
    }
    PQclear(res_ds);

    return 0;
}
