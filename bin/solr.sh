#!/bin/sh
### BEGIN INIT INFO
# Provides:          solr
# Short-Description: Start/stop Solr server.
#
# Description:      This relies on a PID file to check if Solr is running.
#
# Default-Start:    2 3 4 5
# Default-Stop:     0 1 6
# Required-Start:
# Required-Stop:
#
# Author:           Lukas Kahwe Smith <smith@pooteeweet.org>
### END INIT INFO

# Uncomment to debug the script
#set -x

# adjust as needed
BASEDIR=/var/www/XXX
PIDFILE=$BASEDIR/data/solr_index/frontend_prod.pid
PROGRAM1="jetty"
APPCHK=$(ps aux | grep $PROGRAM1 | grep -v "grep $PROGRAM1" | wc -l)

do_start() {
    if [ -f $PIDFILE ]; then
        if [ $APPCHK = '0' ]; then
            rm $PIDFILE
            echo "Removed pid file as Solr wasn't running"
        fi
    fi

    if [ ! -f $PIDFILE ]; then
        cd $BASEDIR
        php symfony lucene:service frontend start
        echo "Solr started"
    else
        echo "Solr is already running"
    fi
}

do_stop() {
    if [ -f $PIDFILE ]; then
        kill $(cat $PIDFILE)
        rm $PIDFILE
        echo "Solr stopped"
    else
        echo "Solr is not running"
    fi
}

do_status() {
    if [ -f $PIDFILE ]; then
          echo "Solr is running [ pid = " $(cat $PIDFILE) "]"
    else
        echo "Solr is not running"
        exit 3
    fi
}

case "$1" in
  start)
        do_start
        ;;
  stop)
        do_stop
        exit 3
        ;;
  restart)
        do_stop
        do_start
        ;;
  status)
        do_status
        ;;
  *)
        echo "Usage: $SCRIPTNAME {start|stop|restart|status}" >&2
        exit 3
        ;;
esac
