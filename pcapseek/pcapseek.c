/* pcapseek.c
 *
 * Xplico System
 * By Gianluca Costa <g.costa@capanalysis.net>
 * Copyright 2013 Gianluca Costa. Web: www.capanalysis.net
 */

#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <signal.h>
#include <sys/wait.h>
#include <signal.h>
#include <pcap.h>
#include <string.h>
#include <stdlib.h>
#include <netinet/if_ether.h>
#include <netinet/ip.h>
#include <netinet/udp.h>
#include <netinet/tcp.h>
#include <arpa/inet.h>
#include <arpa/inet.h>

#include "etypes.h"
#include "ipproto.h"
#include "hdr.h"


#define PS_VER_MAG    1
#define PS_VER_MIN    1
#define PS_VER_REV    0
#define PS_CR         "Copyright 2012-2014 Gianluca Costa.\nThis software is licensed with CC BY-NC-ND.\nThere is NO warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.\n"

#define IPV6_DIM      16  /* ipv6 address size */

static char def;              /* true if flow has been identified */
static char ipv6f;            /* true if IPv6 */
static char udpf;             /* false if tcp */
static unsigned char ipv6_src[IPV6_DIM]; /* IPv6 source */
static unsigned char ipv6_dst[IPV6_DIM]; /* IPv6 destination */
static unsigned long ip_src;    /* IPv4 source */
static unsigned long ip_dst;    /* IPv4 destination */
static unsigned short src_port; /* source port */
static unsigned short dst_port; /* destination port */

static ipv6hdr *n_ipv6;
static struct iphdr *n_ip;

    
static void Usage(char *name)
{
    printf("\n");
    printf("usage: %s [-v] -f <input_file> -s <pkt offset> -o <output_file> [-j <json_file>] [-d <sec>] [-h]\n", name);
    printf("\t-v version\n");
    printf("\t-f input pcap file\n");
    printf("\t-s packet offset\n");
    printf("\t-o output pcap file\n");
    printf("\t-j JSON data files\n");
    printf("\t-d duration\n");
    printf("\t-h this help\n");
    printf("\n");
}


static char Tcp(u_char *bytes, unsigned long len)
{
    struct tcphdr *tcp;
    unsigned short sport, dport;

    if (len < sizeof(struct tcphdr))
        return 0;
        
    tcp = (struct tcphdr *)bytes;

    sport = tcp->source;
    dport = tcp->dest;

    if (def == 0) {
        udpf = 0;
        src_port = sport;
        dst_port = dport;
        def = 1;
        
        return 1;
    }
    else {
        /* check */
        if (src_port == sport) {
            if (ipv6f) {
                if (memcmp(ipv6_src, n_ipv6->saddr.s6_addr, IPV6_DIM) == 0) {
                     if (dst_port == dport) {
                         if (memcmp(ipv6_dst, n_ipv6->daddr.s6_addr, IPV6_DIM) == 0) {
                             return 1;
                         }
                     }
                }
            }
            else {
                if (ip_src == n_ip->saddr) {
                     if (dst_port == dport) {
                         if (ip_dst == n_ip->daddr) {
                             return 1;
                         }
                     }
                }
            }
        }
        else if (src_port == dport) {
            if (ipv6f) {
                if (memcmp(ipv6_src, n_ipv6->daddr.s6_addr, IPV6_DIM) == 0) {
                     if (dst_port == sport) {
                         if (memcmp(ipv6_dst, n_ipv6->saddr.s6_addr, IPV6_DIM) == 0) {
                             return 1;
                         }
                     }
                }
            }
            else {
                if (ip_src == n_ip->daddr) {
                     if (dst_port == sport) {
                         if (ip_dst == n_ip->saddr) {
                             return 1;
                         }
                     }
                }
            }
        }
    }
    
    return 0;
}


static char Udp(u_char *bytes, unsigned long len)
{
    struct udphdr *udp;
    unsigned short udphdr_len, plen;
    unsigned short sport, dport;

    udphdr_len = sizeof(struct udphdr);
    /* packet len */
    if (len < udphdr_len) {
        return 0;
    }
    udp = (struct udphdr *)bytes;
    plen =  ntohs(udp->len);
    /* check lenght packet */
    if (len < plen) {
        return 0;
    }
    
    sport = udp->source;
    dport = udp->dest;

    if (def == 0) {
        udpf = 1;
        src_port = sport;
        dst_port = dport;
        def = 1;
        
        return 1;
    }
    else {
        /* check */
        if (src_port == sport) {
            if (ipv6f) {
                if (memcmp(ipv6_src, n_ipv6->saddr.s6_addr, IPV6_DIM) == 0) {
                     if (dst_port == dport) {
                         if (memcmp(ipv6_dst, n_ipv6->daddr.s6_addr, IPV6_DIM) == 0) {
                             return 1;
                         }
                     }
                }
            }
            else {
                if (ip_src == n_ip->saddr) {
                     if (dst_port == dport) {
                         if (ip_dst == n_ip->daddr) {
                             return 1;
                         }
                     }
                }
            }
        }
        else if (src_port == dport) {
            if (ipv6f) {
                if (memcmp(ipv6_src, n_ipv6->daddr.s6_addr, IPV6_DIM) == 0) {
                     if (dst_port == sport) {
                         if (memcmp(ipv6_dst, n_ipv6->saddr.s6_addr, IPV6_DIM) == 0) {
                             return 1;
                         }
                     }
                }
            }
            else {
                if (ip_src == n_ip->daddr) {
                     if (dst_port == sport) {
                         if (ip_dst == n_ip->saddr) {
                             return 1;
                         }
                     }
                }
            }
        }
    }

    return 0;
}


static char IPv6(u_char *bytes, unsigned long len)
{
    ipv6hdr *ipv6;
    size_t ipv6hdr_len;
    size_t ipv6_len;
    u_char *next;

    ipv6 = (ipv6hdr *)bytes;
    ipv6hdr_len = sizeof(ipv6hdr);
    ipv6_len = ipv6hdr_len + ntohs(ipv6->plen);

    if (ipv6_len > len) {
        return 0;
    }
    next = bytes + ipv6hdr_len;
    len = ipv6_len - ipv6hdr_len;
    if (def == 0) {
        ipv6f = 1;
        memcpy(ipv6_src, ipv6->saddr.s6_addr, IPV6_DIM);
        memcpy(ipv6_dst, ipv6->daddr.s6_addr, IPV6_DIM);
    }
    else
        n_ipv6 = ipv6;
        
    switch (ipv6->nxt) {
    case IP_PROTO_TCP:
        if (def) {
            if (udpf)
                return 0;
        }
        return Tcp(next, len);
        break;
        
    case IP_PROTO_UDP:
        if (def) {
            if (udpf == 0)
                return 0;
        }
        return Udp(next, len);
        break;
    }

    return 0;
}


static char IPv4(u_char *bytes, unsigned long len)
{
    struct iphdr *ip;
    size_t iphdr_len;
    size_t ip_len;
    u_char *next;

    ip = (struct iphdr *)bytes;
    /* IPv- or IPv4 */
    if (ip->version != 4) {
        if (ip->version == 6) {
            return IPv6(bytes, len);
        }
    
        return 0;
    }
    /* IPv4 */
    iphdr_len = ip->ihl << 2;
    ip_len = ntohs(ip->tot_len);
    if (ip_len > len) {
        return 0;
    }
    if (ip->frag_off != 0 && ip->frag_off != 0x40) {
        return 0;
    }
    next = bytes + iphdr_len;
    len = ip_len - iphdr_len;
    if (def == 0) {
        ipv6f = 0;
        ip_src = ip->saddr;
        ip_dst = ip->daddr;
    }
    else
        n_ip = ip;
    
    switch(ip->protocol) {
    case IP_PROTO_TCP:
        if (def) {
            if (udpf)
                return 0;
        }
        return Tcp(next, len);
        break;
        
    case IP_PROTO_UDP:
        if (def) {
            if (udpf == 0)
                return 0;
        }
        return Udp(next, len);
        break;
        
    case IP_PROTO_IPV6:
        if (def) {
            if (ipv6f == 0)
                return 0;
        }
        return IPv6(next, len);
        break;
    }
    
    return 0;
}


static char Ppp(u_char *bytes, unsigned long len)
{
    unsigned char prot;
    int proto_offset;
    unsigned short ppp_prot;
    int nlen;
    u_char *next;
    

    /* PPP HDLC encapsulation */
    if (*bytes == 0xff) {
        proto_offset = 2;
    }
    else {
        /* address and control are compressed (NULL) */
        proto_offset = 0;
    }
    prot = *(bytes + proto_offset);
    
    len = 0;
    if (prot & PFC_BIT) {
        /* Compressed protocol field - just the byte we fetched. */
        ppp_prot = prot;
        nlen = 1;
    }
    else {
        ppp_prot = ntohs(*((uint16_t *)(bytes + proto_offset)));
        nlen = 2;
    }

    /* pdu */
    next = bytes + nlen + proto_offset;
    len -= nlen + proto_offset;

    switch (ppp_prot) {
    case ETHERTYPE_IP:
        if (def) {
            if (ipv6f)
                return 0;
        }
        return IPv4(next, len);
        break;
        
    case ETHERTYPE_IPv6:
        if (def) {
            if (ipv6f == 0)
                return 0;
        }
        return IPv6(next, len);
        break;
    }
    
    return 0;
}


static char PPPoE(u_char *bytes, unsigned long len)
{
    u_char *next;
    pppoe_hdr *pppoeh;
    
    if (sizeof(pppoe_hdr) > len) {
        return 0;
    }
    pppoeh = (pppoe_hdr*)bytes;
    if (pppoeh->code == 0) {
        next = bytes + sizeof(pppoe_hdr);
        len -= sizeof(pppoe_hdr);
        return Ppp(next, len);
    }
    return 0;
}


static char Ethernet(u_char *bytes, unsigned long len)
{
    struct ethhdr *eth;
    u_char *next;
    
    eth = (struct ethhdr *)bytes;

    /* pdu */
    next = bytes + sizeof(struct ethhdr);
    len -= sizeof(struct ethhdr);
    
    switch (ntohs(eth->h_proto)) {
    case ETHERTYPE_IP:
        if (def) {
            if (ipv6f)
                return 0;
        }
        return IPv4(next, len);
        break;

    case ETHERTYPE_IPv6:
        if (def) {
            if (ipv6f == 0)
                return 0;
        }
        return IPv6(next, len);
        break;

    case ETHERTYPE_PPPOES:
        return PPPoE(next, len);
        break;
    }
    
    return 0;
}


static char PktCheck(unsigned int dlt, struct pcap_pkthdr *h, u_char *bytes)
{
    switch (dlt) {
    case DLT_EN10MB:
        return Ethernet(bytes, h->caplen);
        break;
        
    case DLT_RAW:
        return IPv4(bytes, h->caplen);
        break;
        
    case DLT_IEEE802_11:
        break;
        
    case DLT_PPP:
        return Ppp(bytes, h->caplen);
        break;
        
    case DLT_LINUX_SLL:
        //return Sll(bytes, h->caplen);
        break;
    }

    return 0;
}


int main(int argc, char *argv[])
{
    char c;
    char in_file[1024];
    char out_file[1024];
    char json_file[1024];
    char check;
    unsigned long tp;
    unsigned long pk_cnt;
    char errbuf[PCAP_ERRBUF_SIZE];
    pcap_t *cap;
    struct pcap_pkthdr *h;
    const u_char *bytes;
    struct pcap_file_header fh;
    FILE *fp_pcap;
    size_t nwrt, wcnt;
    extern char *optarg;
    extern int optind, optopt;
    FILE *pcap_of;
    unsigned long dur;
    unsigned int dlt;
    struct timeval tstart;
    
    tp = 0;
    check = 0;
    dur = 0;
    def = 0;
    while ((c = getopt(argc, argv, "vf:s:o:hj:d:")) != -1) {
        switch(c) {
        case 'v':
            printf("pcapseek %d.%d.%d\n", PS_VER_MAG, PS_VER_MIN, PS_VER_REV);
            return 0;
            break;

        case 'f':
            sprintf(in_file, "%s", optarg);
            check |= 0x01;
            break;

        case 's':
            tp = atol(optarg);
            check |= 0x02;
            break;

        case 'o':
            sprintf(out_file, "%s", optarg);
            check |= 0x04;
            break;

        case 'j':
            sprintf(json_file, "%s", optarg);
            check |= 0x08;
            break;

        case 'd':
            dur =  atol(optarg);
            break;

        case 'h':
            Usage(argv[0]);
            return 0;
            break;

        case '?':
            printf("Error: unrecognized option: -%c\n", optopt);
            Usage(argv[0]);
            exit(-1);
            break;
        }
    }
    
    printf("pcapseek v%d.%d.%d\n", PS_VER_MAG, PS_VER_MIN, PS_VER_REV);
    printf("%s\n", PS_CR);
    
    if (check != 0x07) {
        Usage(argv[0]);
            exit(-1);
    }
    
    printf("Packet offset: %lu dur: %lu\n", tp, dur);
    
    pk_cnt = 0;
    cap = pcap_open_offline(in_file, errbuf);
    if (cap == NULL) {
        printf("Error:%s\n", errbuf);
        return -1;
    }
    pcap_of = pcap_file(cap);
    
    /* pcap out file */
    fp_pcap = fopen(out_file, "w");
    memset(&fh, 0, sizeof(struct pcap_file_header));
    fh.magic = 0xA1B2C3D4;
    fh.version_major = PCAP_VERSION_MAJOR;
    fh.version_minor = PCAP_VERSION_MINOR;
    fh.snaplen = 65535;
    fh.linktype = pcap_datalink(cap);
    if (fp_pcap != NULL) {
        fwrite((char *)&fh, 1, sizeof(struct pcap_file_header), fp_pcap);
    }
    else {
        printf("Error to open file:%s\n", out_file);
        pcap_close(cap);
        return -1;
    }

    if (tp != 0) {
        fseek(pcap_of, tp, SEEK_SET);
    }
    dlt = pcap_datalink(cap);
    
    while (pcap_next_ex(cap, &h, &bytes) == 1) {
        if (pk_cnt == 0) {
            tstart.tv_sec = h->ts.tv_sec;
        }
        else {
            if (dur != 0 && (h->ts.tv_sec - tstart.tv_sec) > dur) {
                break;
            }
        }     
        if (PktCheck(dlt, h, (u_char *)bytes)) {
            pk_cnt++;
            wcnt = 0;
            do {
                nwrt = fwrite(((char *)h)+wcnt, 1, sizeof(struct pcap_pkthdr)-wcnt, fp_pcap);
                if (nwrt != -1)
                    wcnt += nwrt;
                else
                    break;
            } while (wcnt != sizeof(struct pcap_pkthdr));
            
            wcnt = 0;
            do {
                nwrt = fwrite(((char *)bytes)+wcnt, 1, h->caplen-wcnt, fp_pcap);
                if (nwrt != -1)
                    wcnt += nwrt;
                else
                    break;
            } while (wcnt != h->caplen);
        }
        else if (pk_cnt == 0) {
            /* some layers are missing */
            
        }
    }
    fclose(fp_pcap);
    pcap_close(cap);

    printf("Pkt: %lu\n", pk_cnt);

    return 0;
}
