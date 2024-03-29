<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List of all resource type modules in course
 *
 * @package   moodlecore
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
require_once('../../config.php');
require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot . '/blocks/tutor/lib.php');

$id = required_param('id', PARAM_INT); // course id
$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$PAGE->set_pagelayout('course');
require_course_login($course, true);

// get list of all resource-like modules
$allmodules = $DB->get_records('modules', array('visible'=>1));
$modules = array();
foreach ($allmodules as $key=>$module) {
    $modname = $module->name;
    $libfile = "$CFG->dirroot/mod/$modname/lib.php";
    if (!file_exists($libfile)) {
        continue;
    }
    $archetype = plugin_supports('mod', $modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
    if ($archetype != MOD_ARCHETYPE_RESOURCE) {
        continue;
    }

    $modules[$modname] = get_string('modulename', $modname);
	
    //some hacky nasic logging
    add_to_log($course->id, $modname, 'view all', "index.php?id=$course->id", '');
	
}

$strresources    = get_string('resources');
$stractivities   = get_string('activities');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

//$PAGE->set_url('/course/resources.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strresources);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strresources.' + '.$stractivities);

echo $OUTPUT->header();

$context = get_context_instance(CONTEXT_COURSE,$course->id);
$can_access=false;
if ($roles = get_user_roles($context, $USER->id)) {
	foreach ($roles as $role) {
		if( $role->roleid != 5){ //El 5 es el id del rol estudiante
			$can_access=true;
		}
	}
}

if(!$can_access){
	 redirect("$CFG->wwwroot/course/view.php?id=$course->id") ;

}else{

$mensajes_es = array('Recurso y actividades iniciales', // 0
 			'<b>Atención</b>: Editar el recurso y actividad inicial implica borrar de la base de datos todos los requisitos definidos hasta el momento', // 1
			'Seleccione un recurso o una actividad para editar sus pre-requisitos', // 2
			'Recursos y actividades', // 3
			'Seleccione los pre-requisitos', // 4
			'Recursos: ', // 5
			'Actividades: ' , // 6
			'Cuestionarios: ', // 7
			'Foros: ', // 8
			'Volver', //9
			'Selecciona' // 10
			);

$mensajes_pt_br = array('Recurso e atividade iniciais', // 0
			'<b>Atenção</b>: Editar os recursos e atividades iniciais implica em apagar no banco de dados todos os pré-requisitos definidos até o momento.', // 1
			'Selecione um recurso ou uma atividade para informar seus pré-requisitos', // 2
			'Recursos e atividades:', // 3
			'Selecione os pré-requisitos', // 4
			'Recursos: ', // 5
			'Atividades:', // 6
			'Questionários: ', // 7
			'Fóruns: ', // 8
			'Voltar', // 9
			'Seleciona' // 10
			);

$lang = current_language();
if($lang == 'es'){
	$mensajes = $mensajes_es;
}
if($lang == 'pt_br'){
	$mensajes = $mensajes_pt_br;
}

$modinfo = get_fast_modinfo($course); // $modinfo->cms é um array


/*
$cms = array();
$resources = array();
$assign = array();
*/

foreach ($modinfo->cms as $cm) {

    if (!$cm->uservisible) {
        continue;
    }
	if (!array_key_exists($cm->modname, $modules)) {
        continue;
    }

    if (!$cm->has_view()) {
        // Exclude label and similar
        continue;
    }
	
    $cms[$cm->id] = $cm;	
    $resources[$cm->modname][] = $cm->instance;

	/*$cm->modname; //nome do recurso (page, resource, forum)
	$cm->name; //nome dado ao recurso (leitura1, etc)
	$cm->instance; //nro instancia do recurso
	$cm->id; //id do recurso
	*/
}

// preload instances

foreach ($resources as $modname=>$instances) {
	$resources[$modname] = $DB->get_records_list($modname, 'id', $instances, 'id', 'id,name,intro,introformat,timemodified');		
}

if (!$cms) {
    notice(get_string('thereareno', 'moodle', $strresources), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}


?>	
<html>
 <script language="JavaScript">
   function Selecionar()
   {
     document.listaPreReq.action="tutor_prereq.php?id=<?php echo $id ?>";
     document.forms.listaPreReq.submit();
   }
  </script>
  <script language="JavaScript">
   function Voltar()
   {
     document.listaPreReq.action="tutor_lista.php?id=<?php echo $id ?>&acao=voltar";
     document.forms.listaPreReq.submit();
   }
 </script>


<p><b><?php echo $mensajes[0]; ?></b></p>
<?php
foreach ($cms as $cm) {	
	if ($cm->id == $_SESSION['idRecInic']){
			$_SESSION['recInic'] = $cm->name;			
			echo "Recurso Inicial: ". checkTranslation($cm->name)."<br>";		
		}
}
?>
<?php
foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $_SESSION['idAtivInic']){
		$_SESSION['ativInic']= $cm->name;		
		echo "Atividade Inicial: ".checkTranslation($cm->name)."<br>";				
		}
	}
	
foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $_SESSION['idAtivInic']){
		$_SESSION['ativInic']= $cm->name;		
		echo "Atividade Inicial: ".checkTranslation($cm->name)."<br>";				
		}
	}	
	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) {
			if ($cm->id == $_SESSION['idForumInic']){
				$_SESSION['forumInic'] = $cm->name;				
				echo "Atividade Inicial: ".checkTranslation($cm->name)."<br>";					
			}
		}
	}
?>

<form name="editar" method="post" action="<?php echo "tutor_form.php?id=".$id."&amp;acao=editar"?>" value="editar">
	<input type="submit" style="margin:10px 10px 10px 0; width: 100px;" value="Editar"/>
</form>
<p><?php echo $mensajes[1]; ?></p>

 <?php  $recAtividade = isset($_POST['recAtiv']) ? $_POST['recAtiv'] : NULL;

	foreach ($cms as $cm) {
		if ($cm->id == $recAtividade){
			$_SESSION['selecionada_id'] = $cm->id;			
			$_SESSION['selecionada'] = $cm->name;
		}
	}
    foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $recAtividade){	
		$_SESSION['selecionada_id'] = $cm->id;
		$_SESSION['selecionada'] = $cm->name;		
		}
	}
	foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $recAtividade){	
		$_SESSION['selecionada_id'] = $cm->id;
		$_SESSION['selecionada'] = $cm->name;		
		}
	}	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}			
		if ($cm->id == $recAtividade){	
			$_SESSION['selecionada_id'] = $cm->id;
			$_SESSION['selecionada'] = $cm->name;
			}
		}
$result2 = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND rec_ativ_id = ? AND pre_req_id > ?', array( $id , $_SESSION['selecionada_id'],'0' ));
foreach ($result2 as $res2){	
			$arrPreRec[$res2->rec_ativ_id][] = $res2->pre_req_id;			
	}
foreach($arrPreRec as $rec){		
			foreach ($rec as $re){
				$array_rec[] = $re;
			}
}
$result5 = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND pre_req_id > ?', array( $id , '0' ));
foreach ($result5 as $res5){			
			if ($res5->pre_req_id == $_SESSION['selecionada_id']){
				$arrRecAtivDoPre[$_SESSION['selecionada_id']][] = $res5->rec_ativ_id;
			}
}

foreach($arrRecAtivDoPre as $RADP){		
			foreach ($RADP as $rp){
				$array_atPre[] = $rp;				
			}
}
	
?> 
 <br>
 <p><?php echo $mensajes[2]; ?></p>
	<p><b><?php echo $mensajes[3]; ?></b></p>
  <SELECT style="margin-right:15px; width:120px" NAME="selecionada">
			<OPTION SELECTED><?php echo checkTranslation($_SESSION['selecionada']) ?></OPTION></SELECT>
  <br><br><p><b><?php echo $mensajes[4]; ?></b></p>
   <p><?php echo $mensajes[5]; ?></p>
 <form name="listaPreReq" method="post">  
<?php 
/*
 $teste = array();
 echo "id dos pais do selecionado ". $_SESSION['selecionada_id'];
 
 $teste[] = $_SESSION['selecionada_id'];

$paisretornados = retorna_pais($filhos);
echo "id dos pais do selecionado depois ". count($paisretornados);
foreach ($paisretornados as $paisreturn){
	echo "id dos pais do selecionado".$paisreturn."<br>";
}*/

	foreach ($cms as $cm) {
	
			$recCont++;
			if (!isset($resources[$cm->modname][$cm->instance])) {
				continue;
			}
			//$resource = $resources[$cm->modname][$cm->instance];
			if ($cm->name != $_SESSION['selecionada'] && !in_array($cm->id, $array_atPre)){
				$impRec++;						
			if (in_array($cm->id, $array_rec)){?>		
					<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" checked><?php echo checkTranslation($cm->name)?>
					<br>
		<?php 	}else{?>		
					<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" ><?php echo checkTranslation($cm->name)?>
					<br>
		<?php 	}								
			}else if ($impRec == 0 && $recCont != 1){ 			
					echo "<br>";
					echo 'Não há recursos disponíveis na disciplina para ser pré-requisito';
				}			
		}
		
		echo "<br>";
		
	echo $mensajes[6]; ?><br>	
	<?php foreach ($modinfo->instances['assign'] as $cm) {
		$AtiCont++;
		if (!$cm->uservisible) {
			continue;
		}
		if ($cm->name != $_SESSION['selecionada'] && !in_array($cm->id, $array_atPre)){
			$impAtiv++;			
			if (in_array($cm->id, $array_rec)){?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" checked><?php echo checkTranslation($cm->name)?>
							<br>
				<?php 	}else{?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" ><?php echo checkTranslation($cm->name)?>
							<br>
				<?php }			
		} else if($impAtiv == 0 && $AtiCont != 1){ 
					echo "<br>";
					echo 'Não há atividades disponíveis na disciplina para ser pré-requisito';}
	}	
	echo "<br>";
		
	 echo $mensajes[7]; ?><br>	
	<?php foreach ($modinfo->instances['quiz'] as $cm) {
		$quizCont++;
		if (!$cm->uservisible) {
			continue;
		}
		if ($cm->name != $_SESSION['selecionada'] && !in_array($cm->id, $array_atPre)){
			$impQuiz++;			
			if (in_array($cm->id, $array_rec)){?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" checked><?php echo checkTranslation($cm->name)?>
							<br>
				<?php 	}else{?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" ><?php echo checkTranslation($cm->name)?>
							<br>
				<?php }			
		} else if($impQuiz == 0 && $quizCont != 1){ 
					echo "<br>";
					echo 'Não há questionários disponíveis na disciplina para ser pré-requisito';}
	}	
	echo "<br>";
	echo $mensajes[8];
	echo "<br>"; 
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) { 
		$ForCont++;		
			if ($cm->name != $_SESSION['selecionada'] && !in_array($cm->id, $array_atPre)){
				$impFor++;
				if (in_array($cm->id, $array_rec)){?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" checked><?php echo checkTranslation($cm->name)?>
							<br>
				<?php 	}else{?>		
							<input style="margin-right:5px; margin-left:70px;" type="checkbox" name="prereq[]" value="<?php echo $cm->id ?>" ><?php echo checkTranslation($cm->name)?>
							<br>
				<?php }
			} else if ($impFor == 0 && $ForCont != 1){ 
					echo "<br>";
					echo 'Não há fóruns disponíveis na disciplina para ser pré-requisito';}
		} else{
					echo "<br>";
					echo '<p style="margin-left:70px;"><b>Obs:</b> O fórum de notícias não pode ser usado como pré-requisito</p>';
		}
	}	?>
	
		<br><br><input type="button"  style="width: 100px;" value="<?php echo $mensajes[9]; ?>" onClick="Voltar()"/>
		<input type="button" style="margin-left:65px; width: 100px;" value="<?php echo $mensajes[10]; ?>" onClick="Selecionar()"/>
	</form>

	

</html>
<?php
}

echo $OUTPUT->footer();


