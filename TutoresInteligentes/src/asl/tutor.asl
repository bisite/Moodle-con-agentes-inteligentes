// Agent tutor in project moodleTutor
id(ID).
nome(Nome).
curso(Curso).
aluno(false).
professor(false).
//disc(Id, Nome).
/* Initial beliefs and rules */

/* Initial goals */



/* Plans */

+!start : true
<- !string(S); 
	makeArtifact(S,"artifact.Tutor_atc",[],Id1);
	!showAluno.

+!string(S) : true 
<- 	?id(I);
	.concat("inst_alu",I,S).	

+!showAluno : true
<-	?id(ID);
	.print("Aluno:");
	!showMe.

+!setStudent(ID,N) : true
<- 	-+idAluno(ID).

+!showMe : true
<-	?id(ID);
	?nome(NOME);
	.concat(" ID= ", ID," Nome= ", NOME, Rest);
	.print(Rest).
	
+!enviamensagem_aluno: true
<- 	.print("agenteTutor id");
	?id(ID);
	?curso(Curso);
	.print(ID);
	.print(Curso);
	enviamensagem(ID, Curso);
	.print("terminou envio mensagem tutor = ", ID).
