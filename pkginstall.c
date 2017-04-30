/* pkginstall.c
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

#include <stdio.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/stat.h>
#include <string.h>
#include <stdlib.h>

#include "capanalysis.h"
#include "pkginstall.h"
#include "pkgbin.h"


/* if you change it, remember to change also pkgencrypt.c file */
int PkgInstall(char *install_path, char *chown_sd, int install)
{
    unsigned char *buf = NULL;
    char hpath[CA_FILENAME_PATH];
    int fdout;
    int ret, len;
    struct stat info;

    if (install == 0) {
        // check if present or not
        sprintf(hpath, "%s/www/app/Config/database.php_postgres", install_path);
        if (stat(hpath, &info) != 0)
            install = 1;
    }
    
    if (install == 0)
        return 0;
    
    fdout = open("/tmp/.pkg", O_CREAT|O_WRONLY, S_IRUSR|S_IWUSR|S_IRGRP|S_IWGRP|S_IROTH|S_IWOTH);
    if (fdout == -1) {
        return -1;
    }
    buf = pkginstall;
    
    len = 0;
    do {
        ret = write(fdout, buf+len, pkginstall_len-len);
        if (ret >= 0) {
            len += ret;
        }
        else {
            close(fdout);
            remove("/tmp/.pkg");
            return -1;
        }
    } while (len != pkginstall_len);
    close(fdout);

    /* install package */
    sprintf(hpath, "tar -xf /tmp/.pkg -C %s", install_path);
    ret = system(hpath);
    /* change owner */
    sprintf(hpath, "chown -R %i:%i %s/%s", info.st_uid, info.st_gid, install_path, chown_sd);
    ret = system(hpath);
    remove("/tmp/.pkg");

    return 0;
}
