/* dbinterface.h
 *
 * $Id: $
 *
 * Xplico Lab
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2012 Gianluca Costa. Web: www.xplico.org
 *
 *
 */


#ifndef __DBINTERFACE_H__
#define __DBINTERFACE_H__

#include "config.h"
#include "capanalysis.h"


#define DBCFG_BUFF_SIZE               512
#define DB_VERSION                    "1.1"


/* common */
#define DBINT_QUERY_DIM               10240
#define DBINT_QUERY_DELETE_DS         "DELETE FROM datasets WHERE id='%d';"
#define DBINT_QUERY_DROP_TABLE        "DROP TABLE %s%i;"
#define DBINT_QUERY_CHECK             "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME='datasets';"
#define DBINT_QUERY_VERSION           "SELECT ver FROM versions ORDER BY cdate DESC LIMIT 1;"
#define DBINT_QUERY_SET_VERSION       "INSERT INTO versions (ver) VALUES ('%s');"
#define DBINT_QUERY_DS_DEPTH          "SELECT depth FROM datasets WHERE id='%d';"

/* postgresql */
#define DBINT_1_QUERY_FILE            "INSERT INTO capfiles (dataset_id, data_size, filename, md5, sha1) VALUES (%i, %zu, '%s', '%s', '%s') RETURNING id;"
#define DBINT_1_QUERY_UPDATE_FILE     "UPDATE capfiles SET md5='%s', sha1='%s' WHERE id=%lu;"

/* sql files paths */
#define DBINI_POSTGRES_ITEMS_SQL      "%s/db/postgres_items.sql"
#define DBINI_POSTGRES_IPS_SQL        "%s/db/postgres_ips.sql"
#define DBINI_ITEMS_STR               "items_"
#define DBINI_IPS_STR                 "ips_"

/* upgrade 1.0 to 1.1 */
#define DBINI_POSTGRES_UPG_DATASETS   "SELECT * FROM datasets;"
#define DBINI_POSTGRES_UPG_DS_1       "ALTER TABLE datasets ADD COLUMN depth VARCHAR( 200 ) DEFAULT '-';"
#define DBINI_POSTGRES_UPG_DS_2       "UPDATE datasets SET group_id='2';"
#define DBINI_POSTGRES_UPG_ITM_2      "ALTER TABLE items_%i ADD COLUMN days INTEGER;"
#define DBINI_POSTGRES_UPG_ITM_3      "ALTER TABLE items_%i ADD COLUMN seconds INTEGER;"
#define DBINI_POSTGRES_UPG_ITM_4      "ALTER TABLE items_%i ADD COLUMN metadata VARCHAR( 255 ) DEFAULT '';"
#define DBINI_POSTGRES_UPG_ITEMS      "SELECT id, EXTRACT(EPOCH FROM cdate) AS depo,EXTRACT(EPOCH FROM ctime) AS tepo FROM items_%i;"
#define DBINI_POSTGRES_UPG_ITEM_DAYS  "UPDATE items_%i SET days='%li', seconds='%li' WHERE id='%s';"


typedef enum {
    DB_POSTGRESQL
} dbtype;


typedef struct __dbconf_t dbconf;
struct __dbconf_t {
    dbtype type;                      /* DB type */
    char name[DBCFG_BUFF_SIZE];       /* DB name */
    char user[DBCFG_BUFF_SIZE];       /* DB uaser name */
    char password[DBCFG_BUFF_SIZE];   /* DB password */
    char host[DBCFG_BUFF_SIZE];       /* DB host name */
    char file[DBCFG_BUFF_SIZE];       /* DB file path */
};


int DBIntInit(dbconf *conf);
int DBIntCheckDB(void);
int DBIntCheckVer(char *ver);
int DBIntClose(void);
int DBIntFilePcap(int ds_id, size_t size, const char *name, const char *md5, const char *sha1, unsigned long *id);
int DBIntDeleteDataSet(int ds_id);
int DBIntDeleteFile(int ds_id, int fl_id);
int DBIntDbTable(char *root_dir, int ds_id);
int DBIntDeep(int ds_id, ds_depth *deep);

/* upgrade */
int DBIntSetVer(char *ver);
int DBIntTablesTo1_1(void);
int DBIntContentsTo1_1(void);


#endif /* __DBINTERFACE_H__ */
