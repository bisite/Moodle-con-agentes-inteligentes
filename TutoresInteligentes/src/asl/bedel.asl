// Agent sample_agent in project moodleTutor
id(ID).
nome(NOME).
olhofechado(K).
time(20000).  /* time(20000) = 20 segundos. 24 horas = 86400000 milissegundos*/ 
/* Initial beliefs and rules */

/* Initial goals */

/* Plans */
+!start : true 
<-	?id(ID);
	!string(S); 
	makeArtifact(S ,"artifact.Bedel_Atc",[],Id1);
	setIDcourse(ID);
	!showMe;
	!showTeachers;
	!showStudents;
	.wait(50000);
	!init
	.
	
+!string(S) : true 
<- 	?id(I);
	.concat("inst",I,S).	
	
+!showTeachers : true
<-	show_Teacher(Teachers);
	.print("showTeachers");
	.print(Teachers).
	
+!showStudents : true
<- show_Students(Students);
	.print("showStudents");
   .print(Students).

+!showMe : true
<-	show_Me(View);
	.concat("Tutor Bedel:\n",View,"\n\nPronto!", S);
	.print(S);
	.print("showMe").
	//!init.
	
+!init: true <-
	.print ("init");
	//!verifica_Olho_Fechado;
	//!verifica_data_final_tarefa;  // mudado para que a adaptatividade seja depois de cada aluno fazer a atividade por verifica_avaliacao_aluno
	!verifica_avaliacao_aluno;
	?time(X);
	.print("time ", X);
	.wait(X);
	//!init;
.

+!verifica_avaliacao_aluno: true <- //verifica se teve alguma atividade em que algum aluno foi avaliado.
	verifica_avaliacao_aluno;
	.print("avaliacao_aluno");
	!pegaNidUser.
	
+!pegaNidUser:true<-
	pega_Id_User(Id,IdCourse,HasNext);
	.print("hasnext noPegaNidUser = ",HasNext);
	!devolve_alunos_mensagem(Id,IdCourse,HasNext);
	.wait(5000);
	!pegaNidUser.

+!devolve_alunos_mensagem(Id,IdCourse,HasNext): not(HasNext) <-
	?time(X);
	.print("terminou Bedel = ", ID);
	?id(Id_c);
	.concat("",Id_c,Id_curso);
	!purge_course_cache(Id_curso);
	.print("Limpou cache");
	.print("time ", X);
	.wait(X).
	//!verifica_avaliacao_aluno.

+!devolve_alunos_mensagem(Id,IdCourse,HasNext): HasNext <-
	.print("Id = ",Id);
	.print("hasnext = ",HasNext);
	.concat("usuario id:",Id,N);
	.print("Curso = ",IdCourse);
	.send(N,tell,curso(IdCourse));
	.send(N,tell,id(Id));
	.send(N,achieve,enviamensagem_aluno).
	
+!purge_course_cache(IdCourse):true<-
	purge_course_cache(IdCourse).
	

	

//******************COLOCAR FECHAMENTO DE OLHO APÓS CONFIGURAÇÃO DE GRAFO NO MOODLE*****************************
/* +!verifica_Olho_Fechado: true <- //olhoFechado(K) & K == false <- //feito uma vez s�, no in�cio da disciplina,	
	.print ("verifica olho fechado");
	verifica_olho_fechado;								// o belief do bedel deve ficar como "j� iniciou".
	Fechado = true;
	-+olhoFechado(Fechado);
	//!verifica_avaliacao;
	!verifica_avaliacao_aluno.
*/

	
 /*	!calcula_notas_perfil_alunos.
	
+!calcula_notas_perfil_alunos: true <- //se tarefa foi avaliada ent�o calcula_notas_perfil_alunos
	calcula_notas_perfil_alunos;
	.print("profile grades estimated");
	!calcula_media_perfil.// inserir aqui os dados das tarefas que est�o com data final "vencida" como argumento.

+!calcula_media_perfil: true <- //ap�s calcular a nota do perfil dos alunos deve calcular a media do perfil.
//	calcula_media_perfil;
	.print("average profile estimated");
	!calcula_valores_perfis.

+!calcula_valores_perfis: true<- //ap�s media do perfil deve calcular os valores do perfil.
	calcula_valores_perfis;
	.print("calcula valores perfis");
	.print(final).
	
	*/
	
	/*
+!verifica_data_final_tarefa: true <-//feito uma vez por dia, para verificar se tem tarefas com data de entrega "vencida" e devem ser avaliadas.
	.print("Final date of task verified");	
	verifica_data_final_tarefa; // inserir aqui os dados das tarefas que est�o com data final "vencida" como argumento.
	!verifica_avaliacao. 


+!verifica_avaliacao: true <- //se data_final_tarefa � true, ent�o verifica se j� foi avaliada.
	verifica_avaliacao;
	.print("Evaluation verified");
	!calcula_notas_perfil_alunos.
*/
	
