/* capanalysis.h
 *
 * Capture Analysis
 *
 * $Id:  $
 *
 * Xplico Lab
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2012 Gianluca Costa. Web: www.xplico.org
 *
 */


#ifndef __CAPANA_H__
#define __CAPANA_H__

#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <time.h>

#define CA_FILENAME_PATH         2048
#define CA_FILTER_LINE           2048
#define CA_TBL_ADD               500
#define CA_HASH_STR              1024
#define CA_DS_NAME               "ds_"
#define CA_DS_DIR                "%s/ds_%i"
#define CA_TMP_DIR               "%s/ds_%i/tmp"
#define CA_LOG_DIR               "%s/ds_%i/log"
#define CA_NEW_DIR               "%s/ds_%i/new"
#define CA_RAW_DIR               "%s/ds_%i/raw"
#define CA_DECOD_DIR             "%s/ds_%i/dec"
#define CA_DATA_DIR              "%s/ds_%i/data"
#define CA_FAULT_DIR             "%s/ds_%i/fault"
#define CA_FILTERS_DIR           "%s/ds_%i/filters"
#define CA_HISTORY_DIR           "%s/ds_%i/history"
#define CA_DB_STATUS             "%s/tmp/db.stat"
#define CA_DB_MAKE               "%s/tmp/db.mk"
#define CA_CAPANA_RUN            "/var/run/capana.pid"
#define CA_RT_START_FILE         "realtime_start"
#define CA_RT_STOP_FILE          "realtime_stop"
#define CA_DELETE_DS             "delete"
#define CA_PCAPIP_FILE_PORT      "pcap_ip.port"
#define CA_END_FILE              "ds_end.cfg"
#define DB_T_SQLITE              "sqlite"
#define DB_T_POSTGRES            "postgres"

/* cfg master files */
#define CA_XPLICO_LITE_CFG       "xplico_calite.cfg"
#define CA_XPLICO_POSTGRE_CFG    "xplico_capostgres.cfg"

/* default cert ssl */
#define CA_DEFAULT_CAPANA_CERT   "%s/cfg/capanalysis.pem"

/* capana config file */
#define CA_DEFAULT_POSTGRES_CFG  "%s/cfg/capana_postgres.cfg"


/** boolean type */
typedef unsigned char bool;
#define TRUE     (0==0)
#define FALSE    (!TRUE)

/** default port */
#define CA_PCAP_IP_DEF_PROT    30000
#define CA_PCAP_MAX               50

/** erase session timeout */
#define CA_ERASE_SESSION           2
#define CA_END_TO                600 /* 10 min */
#define CA_GROWTH_TO               1 /* sec */
#define CA_TIME_BETWEEN_PCAPS     30 /* sec, default value */
#define CA_FIND_DS                 5 /* sec, check new DS*/ 

/** task pid list  */
typedef struct _task task;
struct _task {
    int tot;         /* total task in execution */
    pid_t xplico;    /* decoder */
};


typedef enum {
    CA_DP_EL,         /* end date */
    CA_DP_TD,         /* time deep */
    CA_DP_FD,         /* flows deep */
    CA_DP_SZ,         /* size deep */
    CA_DP_NONE
} depth;


typedef struct _ds_depth ds_depth;
struct _ds_depth {
    depth tp;
    union {
        time_t el;
        unsigned long td;
        unsigned long fd;
        size_t sz;
    };
};


/** dataset decoder  */
typedef struct _dsdec dsdec;
struct _dsdec {
    int ds_id;                   /* dataset ID */
    bool run;                    /* task running */
    task pid;                    /* task pid */
    bool end;                    /* closing task */
    bool rm;                     /* remove dataset */
    int end_to;                  /* end timeout [s] */
    char name[CA_FILENAME_PATH]; /* file name */
    size_t size;                 /* size of last file */
    int filenum;                 /* file number */
    time_t growth;               /* file growth */
    bool rt;                     /* real time or not */
    int sd[2];                   /* sockets IPv4 and IPv6 */
    ds_depth deep;
};



#endif /* __CAPANA_H__ */
