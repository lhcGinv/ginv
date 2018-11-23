#!/bin/bash
echo $$start
# 间隔时间
sleep_time=1
# 存放进程号文件目录，每个文件的文件名为当前脚本的pid，记录的内容为本次执行脚本的时间戳，该值一般不做修改。
pid_dir='/data/pid'
if [ ! -d ${pid_dir} ] ; then
    mkdir -p ${pid_dir}
fi
while true
do
    echo $(date '+%s') > ${pid_dir}/$$
    php ../../cli.php v1 demo index >> ./log/demo.log 2>&1
    sleep ${sleep_time}
done