/* session_decoding.h
 * Session decoding monitoring
 *
 * $Id: $
 *
 * Xplico Lab
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2012 Gianluca Costa. Web: www.xplico.org
 *
 *
 */


#ifndef __SESSION_DECODING_H__
#define __SESSION_DECODING_H__

#include "capanalysis.h"
#include "dbinterface.h"

int SeDeInit(char *cert, char *root_dir);
int SeDeFind(char *main_dir, dsdec *tbl, int dim);
int SeDeStart(dbconf *db_c, char *main_dir, int ds, task *pid, bool rt, char *interf, char *filter);
int SeDeEnd(char *main_dir, int ds, task *pid);
char *SeDeFileNew(char *main_dir, int ds, bool *one);
char *SeDeFileDecode(char *main_dir, int ds);
bool SeDeFileActive(char *filepath);
int SeDeRun(task *pid, pid_t chld, bool clear);
int SeDeKill(dsdec *tbl, int id);


#endif /* __SESSION_DECODING_H__ */
