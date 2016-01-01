/* config_file.h
 *
 * $Id: $
 *
 * Xplico Lab
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2012-2014 Gianluca Costa. Web: www.xplico.org
 *
 */


#ifndef __CONFIG_FILE_H__
#define __CONFIG_FILE_H__

/* cfg line */
#define CFG_LINE_COMMENT           '#'
#define CFG_LINE_MAX_SIZE          512

/* directories paths */
#define CFG_PAR_TMP_DIR_PATH       "TMP_DIR_PATH"
#define CFG_PAR_ROOT_DIR           "ROOT_DIR"

/* log dir and name template */
#define CFG_PAR_LOG_DIR_PATH       "LOG_DIR_PATH"
#define CFG_PAR_LOG_NAME_TMP       "LOG_BASE_NAME"

/* DB params */
#define CFG_PAR_DB_TYPE            "DB_TYPE"
#define CFG_PAR_DB_FILE_NAME       "DB_FILENAME"
#define CFG_PAR_DB_HOST            "DB_HOST"
#define CFG_PAR_DB_NAME            "DB_NAME"
#define CFG_PAR_DB_USER            "DB_USER"
#define CFG_PAR_DB_PASSWORD        "DB_PASSWORD"

/* generic params */
#define CFG_PAR_LOG_NAME           "LOG_NAME"
#define CFG_PAR_LOG_LEVELS         "LOG_MASK"
#define CFG_PAR_PCAP_FILES_TIME    "TIME_BETWEEN_PCAPS"
#define CFG_PAR_MD5_ENABLED        "MD5_ENABLED"

/* SSL cert */
#define CFG_SSL_CERT               "SSL_CERT_KEY"

int CfgParIsComment(char *line);
int CfgParamStr(const char *cfg_file, const char *rparam, char *ret_val, int rsize);

#endif
