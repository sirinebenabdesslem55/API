#!/bin/bash
  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

# Add execution for this script in the crontab
# Used to defer some mail sending to this script, asynchronous
# Valid when thousands of mails need to be sent
# add execution of this shell script in the crontab
# example: send mails on every minute
# * * * * * /var/www/vhosts/mydomain.org/httpdocs/scripts/mailer.sh
  
EBDIR=`dirname $0`/..
export HTTP_HOST=`hostname`
cd $EBDIR
php ./mailer.php
