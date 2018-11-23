
tick(2000).//2 segundos (valor em milisegundos)
/* Initial beliefs and rules */

/* Initial goals */

!criaArtefato.

/* Plans */

+!criaArtefato : true
<-	makeArtifact("Gerenciador","artifact.Gerente_Atc",[],Id1);
	inicia;
	!start
	.

+!start : true 
<-	!criaNagentes;
	verificaNovos;
	?tick(Time);
	.wait(Time);	
	!start;
.

+!criaNagentes : true
<- 	getNaIdUser(Id, Name, HasNext);
	!criarUser(Id,Name,HasNext);
	getNaIdCourse(Ids,Names,HasNexts);
	!criarCourse(Ids,Names,HasNexts)
.

+!criarUser(Id, Name,HasNext): not(HasNext)
<-.wait(50)
.

+!criarUser(Id,Name,HasNext) : HasNext
<-	!criaAgent("usuario id:",Id,Name,"tutor.asl");
	getNaIdUser(Ids, Named,HasNextf);
	!criarUser(Ids,Named, HasNextf);
.

+!criarCourse(Id, Name,HasNext): not(HasNext)
<-.wait(50)
.

+!criarCourse(Id,Name,HasNext) : HasNext
<-	!criaAgent("disciplina id:",Id,Name,"bedel.asl");
	getNaIdUser(Ids, Named,HasNextf);
	!criarCourse(Ids,Named, HasNextf);
.


+!criaAgent(Title,Id,NAME,Source) : true
<- 	.concat(Title,Id,N);
	.create_agent(N,Source,[agentArchClass("c4jason.CAgentArch")]);
	.send(N,tell,id(Id));
	.send(N,tell,nome(NAME));
	.send(N,achieve,start);
	.

/*.send(AGENT_NAME, tell, BELIEF(value)); conta algo para um agente e faz ele acreditar, equivalente a +belief(value)
.send(AGENT_NAME, untell, BELIEF(value)); conta algo para um agente e faz ele deixar de acreditar, equivalente a -belief(value)
.send(AGENT_NAME, achieve, GOAL(value)); adiciona um novo objetivo no agente, equivalente a !goal ou !goal(value);*/