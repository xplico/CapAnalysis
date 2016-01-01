# Makefile
#
# $Id: $
#
# Xplico System
# By Gianluca Costa <g.costa@xplico.org>
# Copyright 2013 Gianluca Costa. Web: www.xplico.org
#
#

# root directory
ROOT_DIR = $(shell pwd)
ifndef INSTALL_DIR
INSTALL_DIR = /opt/capanalysis
endif

# sub directory
SUBDIRS = 

# xplico library
XPL_LIB =

# src file
SRC = pcapseek.c

# compilation
INCLUDE_DIR = -I$(ROOT_DIR)/include
LDFLAGS = -L$(ROOT_DIR) -lpcap
CFLAGS = -rdynamic $(INCLUDE_DIR) -Wall -g -ggdb -fPIC -D_FILE_OFFSET_BITS=64

# performance
ifdef GPROF
CFLAGS += -pg
endif


# To make it visible
export CC CCPP ROOT_DIR CFLAGS LDFLAGS INCLUDE_DIR INSTALL_DIR

all: subdir pcapseek

help:
	@echo "Flags:"
	@echo "    VER=<string>  --> string is the release name, otherwise the date is the name"
	@echo "    GPROF=1       --> enable gprof compilation"
	@echo " "
	@echo "Comands:"
	@echo "    help    --> this help"
	@echo "    clean   --> clean"
	@echo "    tgz     --> project snapshot"
	@echo "    install --> install in /opt/xplico"
	@echo " "

# version name
ifndef VER
VER = -$(shell date +%Y_%m_%d)
endif

pcapseek: $(SRC:.c=.o)
	$(CC) $(CFLAGS) -o $@ $(SRC:.c=.o) $(LDFLAGS)


subdir:
	@for dir in $(SUBDIRS) ; \
	   do $(MAKE) -C $$dir || exit 1; \
	 done


clean: reset
	@for dir in $(SUBDIRS) ; do $(MAKE) -C $$dir clean ; done
	rm -f include/*~
	rm -f pcapseek *.o *~ *.log .depend val.* *.expand


install: all
	mkdir -p $(INSTALL_DIR)
	mkdir -p $(INSTALL_DIR)/bin
	cp -a pcapseek $(INSTALL_DIR)/bin
	strip -s $(INSTALL_DIR)/bin/pcapseek

.PHONY: reset
reset:
	rm -rf tmp/*


%.o: %.c
	$(CC) $(CFLAGS) -c -o $@ $< 


tgz: clean
	cd ..; tar cvzf pcapseek$(VER).tgz --exclude cscope.files --exclude cscope.out --exclude CVS --exclude release pcapseek
	mkdir -p release
	mv ../pcapseek$(VER).tgz release
	rm -f release/*.gpg



.depend: $(SRC)
	$(CC) -M $(CFLAGS) $(SRC) > $@


sinclude .depend
