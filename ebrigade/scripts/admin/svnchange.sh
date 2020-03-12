#!/bin/bash
export PATH=/bin:$PATH
for file in `ls lib/fpdf/*.php scripts/*.sh *.php`
do
echo $file
cat $file | sed s/"2019 Nicolas"/"2020 Nicolas"/g | \
sed s/"version\: 5\.0"/"version\: 5\.1"/g \
 > ${file}_2
mv ${file}_2 ${file}
done
