valgrind -v --track-fds=yes --error-limit=no --show-reachable=yes --leak-check=full --leak-resolution=high --log-file=val.%p ./xplico -c config/xplico_canone.cfg -m pcap -d $1
