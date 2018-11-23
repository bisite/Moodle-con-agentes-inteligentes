#!/bin/bash
STR=$(mysql -u root -ptinteligentes00 -D moodle -e "select id from mdl_tutor_perfil where nome='lanzar' ")
fecha=$(date)
if [ -z "$STR" ];
then
     echo $fecha
     echo "Aun no hay que lanzar jason\n"

else
     echo $(date)
     echo "\nEliminando registro lanzar...."
     echo "Eliminando registro de mdl_tutor_bedel_curso"
     var0=$(mysql -u root -ptinteligentes00 -D moodle -e "delete from mdl_tutor_perfil where nome='lanzar' ")
     var1=$(mysql -u root -ptinteligentes00 -D moodle -e "delete from mdl_tutor_bedel_curso")

     echo "Lanzando agentes......\n"
     cd /home/raul/Downloads/Tutores/TutoresInteligentes-master/bin && ant

fi

