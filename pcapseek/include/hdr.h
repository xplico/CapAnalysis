/* hdr.h
 *
 * $Id: $
 *
 * Xplico System
 * By Gianluca Costa <g.costa@xplico.org>
 * Copyright 2009 Gianluca Costa & Andrea de Franceschi. Web: www.xplico.org
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
 */


#ifndef __HDR_H__
#define __HDR_H__

#include <sys/types.h>
#include <sys/time.h>
#include <arpa/inet.h>
#include <bits/endian.h>

/* PPP protocol field compression */
#define PFC_BIT 0x01


/*
 * Definition for internet protocol version 6.
 * RFC 1883
 */
typedef struct _ipv6hdr ipv6hdr;
struct _ipv6hdr {
    unsigned char prio:4;  /* 4 bits priority */
    unsigned char ver:4;   /* 4 bits version */
    unsigned char flow[3]; /* 20 bits of flow-ID */
    unsigned short plen;   /* payload length */
    unsigned char  nxt;	   /* next header */
    unsigned char  hlim;   /* hop limit */
    struct in6_addr saddr; /* source address */
    struct in6_addr daddr; /* destination address */
};

typedef struct _pppoe_hdr pppoe_hdr;
struct _pppoe_hdr {
#if __BYTE_ORDER == __LITTLE_ENDIAN
    unsigned char ver:4;
    unsigned char type:4;
#elif __BYTE_ORDER == __BIG_ENDIAN
    unsigned char type:4;
    unsigned char ver:4;
#else
# error "Please fix <bits/endian.h>"
#endif
    unsigned char code;
    unsigned short sess_id;
    unsigned short len;
};

#endif
