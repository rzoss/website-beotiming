#!/bin/bash

n=0
while read curline; do
    n=`expr $n + 1`
    echo "$curline" > time$n.txt
    echo "Time $n written."
done < time.txt

echo "$n" > time.dat


