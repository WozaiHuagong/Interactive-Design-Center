# 看看是否需要执行git pull
cd /home/wwwroot/default/
if [ -f "need_pull.txt" ]; then
  rm -f "need_pull.txt"
  git pull
fi