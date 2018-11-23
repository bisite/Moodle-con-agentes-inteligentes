#!/bin/bash
STR=$(mysql -u root -ptinteligentes00 -D moodle -e "select id from mdl_tutor_perfil where nome='matar' ")
fecha=$(date)
if [ -z "$STR" ];
then
     echo $fecha
     echo "Aun no hay que matar jason\n"
else
     echo $(date)
     echo "Eliminando registro lanzar...."
     var0=$(mysql -u root -ptinteligentes00 -D moodle -e "delete from mdl_tutor_perfil where nome='matar' ")

     echo "Matar agentes......"
     var=$(ps -ef | grep jasonTutor.mas2j | awk '{print $2}' | head -n 1)
     echo $var
     kill -9 $var
fi
