# Makefile
#
# CapAnalysis
# By Gianluca Costa <g.costa@xplico.org>
# Copyright 2012-2016 Gianluca Costa. Web: www.capanalysis.net
#
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#


# optimization
#O3=yes

# root directory
ROOT_DIR = $(shell pwd)
DIR_NAME = $(shell basename $(ROOT_DIR))

ifndef DEFAULT_DIR
DEFAULT_DIR = /opt/capanalysis
endif
ifdef DESTDIR
INSTALL_DIR = $(DESTDIR)/$(DEFAULT_DIR)
else
INSTALL_DIR = $(DEFAULT_DIR)
endif

# sub directory
SUBDIRS = pcapseek

# xplico modules
XMODULES= dis_dns_ca.so dis_tcp_ca.so dis_udp_ca.so disp_none.so disp_capostgres.so cap_ca.so cap_pcap.so dis_pcapf.so dis_pol.so dis_eth.so dis_pppoe.so dis_ppp.so dis_ip_nocheck.so dis_ipv6.so dis_tcp_soft.so dis_tcp_soft_nocheck.so dis_udp_nocheck.so dis_vlan.so dis_sll.so dis_ieee80211.so dis_llc.so dis_ppi.so dis_prism.so dis_ipsec.so dis_ipsec_ca.so dis_null.so dis_radiotap.so dis_mpls.so

MODULE_PATH = modules

# capanalysis library
XPL_LIB = 

# src file
SRC = capanalysis.c session_decoding.c dbinterface.c pkginstall.c log.c

# compilation
INCLUDE_DIR = -I$(ROOT_DIR)/include
LDFLAGS = -L$(ROOT_DIR) -lpthread -lpcap -lssl -lcrypto
CFLAGS = -rdynamic $(INCLUDE_DIR) -Wall -fPIC -D_FILE_OFFSET_BITS=64

# pedantic statistics
CFLAGS += $(INCLUDE_DIR)

#CFLAGS += -DVER_PRO=1

# optmimization
ifdef O3
CFLAGS += -O3
else
#CFLAGS += -g -ggdb -dr
CFLAGS += -g -ggdb -O0
endif

# performance
ifdef GPROF
CFLAGS += -pg
endif

# architeture type
ifdef CROSS_COMPILE
CC = $(CROSS_COMPILE)gcc
STRIP = $(CROSS_COMPILE)strip
else
STRIP = strip
CFLAGS += -DXPL_X86=1
endif

# sqlite version
LDFLAGS += -lsqlite3
# postgres
LDFLAGS += -lpq

# To make it visible
export CC CCPP ROOT_DIR CFLAGS LDFLAGS INCLUDE_DIR INSTALL_DIR GEOIP_LIB

all: subdir capanalysis cpxplico check_version

help:
	@echo "Flags:"
	@echo "    VER=<string>      --> string is the release name, otherwise the date is the name"
	@echo "    GPROF=1           --> enable gprof compilation"
	@echo "    O3=1              --> enable optimization"
	@echo " "
	@echo "Comands:"
	@echo "    pkgbin  --> this makes the UI package"
	@echo "    help    --> this help"
	@echo "    reset   --> delete default tmp data"
	@echo "    clean   --> clean"
	@echo "    tgz     --> project snapshot"
	@echo "    install --> install in $(INSTALL_DIR)"
	@echo "    check_version --> check version"
	@echo " "

# version name
ifndef VER
VER = $(shell date +%Y_%m_%d)
endif

capanalysis: $(SRC:.c=.o)
	$(CC) $(CFLAGS) -o $@ $(SRC:.c=.o)  $(LDFLAGS)
	mkdir -p tmp
	mkdir -p xdecode

pkgbin:
	./uipkg.sh

subdir:
	@for dir in $(SUBDIRS) ; \
	   do $(MAKE) -C $$dir || exit 1; \
	 done

cpxplico:
	@cp ../xplico/xplico .
	@mkdir -p $(MODULE_PATH)
	@for mod in $(XMODULES) ; \
	   do cp ../xplico/modules/$$mod $(MODULE_PATH); \
	done

clean: reset
	@for dir in $(SUBDIRS) ; do $(MAKE) -C $$dir clean; done
	rm -f capanalysis xplico *.o *~ *.log .depend val.* *.expand
	rm -rf debian/capanalysis*
	rm -rf $(MODULE_PATH)
	rm -f */*~
	rm -f */*/*~
	rm -f www*/app/*~
	rm -f www*/app/*/*~
	rm -f www*/app/*/*/*~
	rm -f www*/app/tmp/sessions/*
	rm -f www*/app/tmp/cache/*/*
	rm -f www*/app/tmp/logs/*
	rm -f www*/app/tmp/tests/*
	rm -f *.tgz


installcp: all 
	rm -rf $(INSTALL_DIR)/*
	mkdir -p $(INSTALL_DIR)
	chmod 777 $(INSTALL_DIR)
	mkdir -p $(INSTALL_DIR)/bin
	mkdir -p $(INSTALL_DIR)/tmp
	mkdir -p $(INSTALL_DIR)/bin/modules
	mkdir -p $(INSTALL_DIR)/db
	mkdir -p $(INSTALL_DIR)/db/postgresql
	mkdir -p $(INSTALL_DIR)/cfg
	mkdir -p $(INSTALL_DIR)/log
	cp -a capanalysis xplico pcapseek/pcapseek $(INSTALL_DIR)/bin
	strip -s $(INSTALL_DIR)/bin/capanalysis $(INSTALL_DIR)/bin/xplico $(INSTALL_DIR)/bin/pcapseek
	cp -a $(MODULE_PATH)/* $(INSTALL_DIR)/bin/modules
	strip -s  $(INSTALL_DIR)/bin/modules/*.so
	cp -a LICENSE $(INSTALL_DIR)/
	cp db/postgresql/items.sql $(INSTALL_DIR)/db/postgres_items.sql
	cp db/postgresql/ips.sql $(INSTALL_DIR)/db/postgres_ips.sql
	cp -a db/postgresql $(INSTALL_DIR)/db/
	cp -a config/canalysis.cfg config/xplico_capostgres.cfg config/apache_capana.conf $(INSTALL_DIR)/cfg
	cp -a wwwinst $(INSTALL_DIR)/www
ifeq ($(wildcard GeoLiteCity.dat), GeoLiteCity.dat)
	cp -a GeoLiteCity.dat $(INSTALL_DIR)/bin/
endif
ifeq ($(wildcard GeoLiteCityv6.dat), GeoLiteCityv6.dat)
	cp -a GeoLiteCityv6.dat $(INSTALL_DIR)/bin/
endif
ifeq ($(wildcard GeoIP.dat), GeoIP.dat)
	cp -a GeoIP.dat $(INSTALL_DIR)/bin/
endif
ifeq ($(wildcard GeoIPv6.dat), GeoIPv6.dat)
	cp -a GeoIPv6.dat $(INSTALL_DIR)/bin/
endif

# install and permission
ifndef DESTDIR
install: installcp
	chmod 777 $(INSTALL_DIR)
	chmod 777 $(INSTALL_DIR)/cfg
else
install: installcp
	chmod 777 $(INSTALL_DIR)
	chmod 777 $(INSTALL_DIR)/cfg
	mkdir -p $(DESTDIR)/etc/apache2/sites-available/
	mkdir -p $(DESTDIR)/etc/apache2/sites-enabled/
	cp $(INSTALL_DIR)/cfg/apache_capana.conf $(DESTDIR)/etc/apache2/sites-available/capana.conf
endif


.PHONY: check_version
check_version:
	@./check_version.sh none


.PHONY: reset
reset:
	rm -rf tmp/*
	rm -rf xdecode


tgz: clean
	cd ..; tar cvzf capanalysis-$(VER).tgz --exclude cscope.files --exclude cscope.out --exclude CVS --exclude ds --exclude .svn --exclude release --exclude .svn --exclude .git $(DIR_NAME)
	mkdir -p release
	mv ../capanalysis-$(VER).tgz release
	rm -f release/*.gpg


%.o: %.c
	$(CC) $(CFLAGS) -c -o $@ $< 


.depend: $(SRC)
	$(CC) -M $(CFLAGS) $(SRC) > $@


sinclude .depend
