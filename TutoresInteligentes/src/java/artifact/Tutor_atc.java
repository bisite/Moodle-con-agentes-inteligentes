package artifact;
import java.util.ArrayList;
import java.sql.ResultSet;
import abs.User;
import java.sql.SQLException;
import cartago.Artifact;
import cartago.OPERATION;
import cartago.OpFeedbackParam;
import java.io.UnsupportedEncodingException;


public class Tutor_atc extends db_art {
	public User aluno;
//	private ArrayList<String> lista_Id_alunos;
	
//	void init(){
	//	lista_Id_alunos = new ArrayList<String>();
//	}
	
	@OPERATION
	public void setAluno(User aluno){
		this.aluno = aluno;
		this.aluno.setAgent();
	}
	
	@OPERATION
	public void mostrarDisciplinas(OpFeedbackParam<String> s){
		s.set("logo");
	}
	
	@OPERATION
	public void enviamensagem(String id, String curso)throws SQLException, ClassNotFoundException, UnsupportedEncodingException {
		System.out.println("entre no método enviamensagem do tutor = "+ id);
		System.out.println("entre no método enviamensagem do curso = "+ curso);
		String string;
	//	this.conexaoBD("enviamensagem tutor");
		string = "SELECT firstname FROM mdl_user WHERE id =" + id;
		ResultSet rs_nome = this.select(string);
		rs_nome.next();
		String nome_aluno = rs_nome.getString("firstname");
		System.out.println("nome do aluno = "+ nome_aluno);
		string = "SELECT * FROM mdl_tutor_alunos_avaliados WHERE id_curso = '"+ curso + "' AND id_aluno = '"+ id +"' ORDER BY id DESC LIMIT 1";
		ResultSet rs_aluno_ultima_at = this.select(string);
		String id_grade_item = "";
		int quant_avaliacoes = 0;
		int nro_calculo = 0;
		int anterior_nro_calculo = 0;
		rs_aluno_ultima_at.next();
		nro_calculo = rs_aluno_ultima_at.getInt("nro_calculo");
		id_grade_item = rs_aluno_ultima_at.getString("id_grade_item");
		System.out.println("id tutor_alunos_avaliados = "+rs_aluno_ultima_at.getString("id")+" - id_curso = "+rs_aluno_ultima_at.getString("id_curso")+" - id_grade_item = "+id_grade_item+" - nro_calculo = "+nro_calculo);
		string = "SELECT COUNT(id_grade_item) FROM mdl_tutor_alunos_avaliados WHERE id_grade_item = "+id_grade_item+ " AND id_aluno = "+id;
		//conta quantas vezes o aluno já fez a mesma atividade
		ResultSet rs_quant = this.select(string);
		rs_quant.next();
		String message = "";
		quant_avaliacoes = rs_quant.getInt("COUNT(id_grade_item)");
		System.out.println("quant_avaliacoes = "+ quant_avaliacoes);
		String id_module = this.converte_id_grade_item_em_id_module(id_grade_item, curso);
		boolean eh_ultima = false;
		if (id_grade_item.equals("182")){
			eh_ultima = true;
		}
		if (nro_calculo == 0 && quant_avaliacoes <= 1){
			//é a primeira vez que o aluno fez essa atividade e não teve mais do que 2.5
			//avisa que vão abrir atividades opcionais e fala para continuar tentando
			System.out.println("Entrei no quant_avaliações <=1");
			string = "SELECT pre_req_id FROM mdl_tutor_dependencia WHERE rec_ativ_id = "+id_module+" AND curso_id = "+ curso;
			ResultSet rs_pre = this.select(string);
			boolean eh_inicial = false;
			while(rs_pre.next()){
				if(rs_pre.getString("pre_req_id").equals("0")){
					eh_inicial = true;
				}	
			}
			if (eh_ultima){
				/*MODIFIQUEI NO BEDEL PARA QUE MESMO NÃO TENDO NOTA MAIOR A 25, SE ESTIVER NA ÚLTIMA ELE NÃO PRECISA REFAZER, ELE TERMINA O CURSO MESMO ASSIM*/
				message = "Olá, "+ nome_aluno+", \n\n O seu desempenho na atividade que respondeu não foi suficiente para finalizar o curso. Não desanime!\n\n Abri novamente a atividade para que tente de novo. Vamos lá! Tá quase acabando!";
			} else if (eh_inicial){
				message = "Olá, "+ nome_aluno+", \n\n O seu desempenho na atividade que respondeu não foi suficiente para avançar no curso. Não desanime!\n\n Abri de novo a atividade que tinha feito para que tente novamente. Vamos lá! \n Estarei acompanhando-o durante o curso!";
			}else{
				message = "Olá, "+ nome_aluno+", \n\n O seu desempenho na atividade que respondeu não foi suficiente para avançar no curso. Não desanime! \n\n Abri alguns tópicos para que você possa relembrar alguns conceitos que podem lhe ajudar a ir melhor na próxima vez. Eles estão no Módulo de pré-requisitos. Estes tópicos são opcionais, mas é uma boa ideia revisá-los! :) \n Também, abri a atividade para que tente novamente respondê-la e possa continuar o curso. Vamos lá! \n Boa sorte! Estarei acompanhando-o durante o curso!";
			}
			this.insere_mensagem(nome_aluno, id, message, curso);
		}else if (nro_calculo == 0 && quant_avaliacoes > 1){
			// a última atividade não teve mais que 2.5 e ele já fez a atividade mais de uma vez
			//envia mensagem para continuar tentando e ler de novo o conteúdo
			if (eh_ultima){
				/*MODIFIQUEI NO BEDEL PARA QUE MESMO NÃO TENDO NOTA MAIOR A 25, SE ESTIVER NA ÚLTIMA ELE NÃO PRECISA REFAZER, ELE TERMINA O CURSO MESMO ASSIM*/
				message = "Olá, "+ nome_aluno+", \n\n Ainda não conseguiu passar o teste, não tem problema! Às vezes precisamos ler de novo para aprendermos melhor. \n Tenta lendo os textos em que você sente mais dificuldade! E, depois, faça novamente a atividade para que possa finalizar o curso! \n Você consegue. Vamos lá!";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}else if (quant_avaliacoes == 2){
				message = "Olá, "+ nome_aluno+", \n\n Ainda não conseguiu passar esse exercício, não tem problema! Às vezes precisamos ler de novo para aprendermos melhor. \n Tenta ler os textos de novo! E se não tiver feito ainda as atividades opcionais, tenta fazê-las! Elas podem te ajudar :) \n Depois, faça novamente a atividade que ainda não conseguiu passar, para que possa continuar o curso! \n Você irá melhor. Vamos lá!";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}else{
				// Aqui verifica o perfil da atividade, como o aluno já tentou resolver mais de duas vezes e não conseguiu nota suficiente para avançar, o agente abre uma atividade um pouco mais fácil.
				System.out.println("Entrei para abrir nova atividade um pouco mais fácil para o aluno");
				System.out.println("id_module = "+ id_module);
				string = "SELECT perfil_id FROM mdl_tutor_rec_at_perfil WHERE rec_ativ_id = "+id_module;
				ResultSet rs_perfil_id = this.select(string);
				rs_perfil_id.next();
				String perfil_do_modulo = rs_perfil_id.getString("perfil_id");
				System.out.println("perfil_do_modulo = "+ perfil_do_modulo);
				//Para saber o nível do módulo que o aluno está tendo dificuldade (1, 2, 3 ou 4)
				String id_grade_item_anterior = this.verifica_atividade_anterior(id, curso);
				System.out.println("id_grade_item_anterior = "+ id_grade_item_anterior);
				id_module = this.converte_id_grade_item_em_id_module(id_grade_item_anterior, curso);
				System.out.println("id_module depois do id_Grade_item_anterior = "+ id_module);
				ArrayList<Associacao_id_module_perfil_id> lista_atividades_id_module_perfil = new ArrayList<Associacao_id_module_perfil_id>();
				lista_atividades_id_module_perfil = this.devolve_atividades_para_mostrar(curso, id_module);
				System.out.println("lista_atividades_id_module_perfil = "+ lista_atividades_id_module_perfil);
				String id_module_atividade = "";
				String id_module_atividade_perfil = "";
				int id_grupo_criado = 0;
				if(perfil_do_modulo.equals("2")){
				System.out.println("Entrei em perfil do modulo equals 2");					
				//se entra aqui o aluno está em uma atividada intermediária. O agente mostra a básica
					for (int i = 0; i < lista_atividades_id_module_perfil.size(); i ++){
						System.out.println("lista_atividades_id_module_perfil.size() em 2 = "+ lista_atividades_id_module_perfil.size());					
						id_module_atividade_perfil = lista_atividades_id_module_perfil.get(i).getPerfilId();
						System.out.println("id_module_atividade_perfil em 2 = "+ id_module_atividade_perfil);
						id_module_atividade = lista_atividades_id_module_perfil.get(i).getIdModule();
						System.out.println("id_module_atividade em 2= "+ id_module_atividade);
						if (id_module_atividade_perfil.equals("1")){
							System.out.println("Se entra aqui quer dizer que essa atividade é básica e é para ser mostrada ao aluno que não consegue resolver a intermediária.");
							//Se entra aqui quer dizer que essa atividade é básica e é para ser mostrada ao aluno que não consegue resolver a intermediária.
							id_grupo_criado = this.verifica_availability_manda_inserir_grupo(id, curso, id_module_atividade, id_module_atividade_perfil);
							String str =  "'{\"op\":\"&\",\"c\":[{\"type\":\"group\",\"id\":"+ id_grupo_criado +"}],\"showc\":[false]}'";
							string = "UPDATE mdl_course_modules SET availability = "+ str +", visible = '1', visibleold = '1' WHERE id="  + id_module_atividade;
							this.conexaoBD("enviamensagem tutor 2");
							this.update(string);
							this.fecharConexao();
						}
					}	
					message = "Olá, "+ nome_aluno+", \n\n Está difícil resolver essa atividade, certo?\n Vou te passar um outro grupo de exercícios para ver se você consegue ir melhor! \n Olha lá e tenta resolvê-los!";
					this.insere_mensagem(nome_aluno, id, message, curso);
				} else if (perfil_do_modulo.equals("3")){
				System.out.println("Entrei em perfil do modulo equals 3");					
				//se entra aqui o aluno está em uma atividada avançada. O agente mostra a intermediária
					for (int i = 0; i < lista_atividades_id_module_perfil.size(); i ++){
						System.out.println("lista_atividades_id_module_perfil.size() em 3 = "+ lista_atividades_id_module_perfil.size());					
						id_module_atividade_perfil = lista_atividades_id_module_perfil.get(i).getPerfilId();
						System.out.println("id_module_atividade_perfil em 3 = "+ id_module_atividade_perfil);
						id_module_atividade = lista_atividades_id_module_perfil.get(i).getIdModule();
						System.out.println("id_module_atividade em 3 = "+ id_module_atividade);
						if (id_module_atividade_perfil.equals("2")){
							System.out.println("Se entra aqui quer dizer que essa atividade é intermediária e é para ser mostrada ao aluno que não consegue resolver a avançada.");
						//Se entra aqui quer dizer que essa atividade é intermediária e é para ser mostrada ao aluno que não consegue resolver a avançada.
							id_grupo_criado = this.verifica_availability_manda_inserir_grupo(id, curso, id_module_atividade, id_module_atividade_perfil);
							String str =  "'{\"op\":\"&\",\"c\":[{\"type\":\"group\",\"id\":"+ id_grupo_criado +"}],\"showc\":[false]}'";
							string = "UPDATE mdl_course_modules SET availability = "+ str +", visible = '1', visibleold = '1' WHERE id="  + id_module_atividade;
							this.conexaoBD("enviamensagem tutor 3");
							this.update(string);
							this.fecharConexao();
						}
					}
					message = "Olá, "+ nome_aluno+", \n\n Está bem difícil resolver essa atividade, certo? Vou te passar outro grupo de exercícios que não têm tanta dificuldade, para ver se você consegue ir melhor! \n\n Vamos lá, você consegue resolver esses!";
					this.insere_mensagem(nome_aluno, id, message, curso);
				}else{
					//se entra aqui o aluno está em uma atividade básica ou geral, que não tem outra opção mais fácil para resolver
					message = "Olá, "+ nome_aluno+", \n\n Ainda não conseguiu passar esse exercício, não tem problema! Às vezes precisamos ler mais de uma vez para entender os conceitos.\n\n Tenta ler os textos de novo! Você irá melhor. Vamos lá!";
					this.insere_mensagem(nome_aluno, id, message, curso);
				}
			}
		}else if (nro_calculo != 0){
			//aluno fez atividade e tirou mais do que 2.5 nela
			//pega o nro_calculo anterior no BD
			string = "SELECT * FROM mdl_tutor_alunos_avaliados WHERE id_aluno = '"+ id + "' AND id_curso = '"+ curso +"' ORDER BY id DESC";
			ResultSet rs_aluno = this.select(string);
			ArrayList<Integer> perfil = new ArrayList<Integer>();
			ArrayList<Double> media = new ArrayList<Double>();
			ArrayList<Integer> lista_id_grade_item = new ArrayList<Integer>();
			int perfil_anterior = 0;
			double media_anterior = 0;
			while(rs_aluno.next()){
				perfil.add(rs_aluno.getInt("id_perfil"));
				media.add(rs_aluno.getDouble("media"));
				lista_id_grade_item.add(rs_aluno.getInt("id_grade_item"));
			}
			if (eh_ultima){
				/* ESTA PARTE SERÁ MUDADA QUANDO FOR INSERIDA A OPÇÃO DE CONFIGURAÇÃO DA ÚLTIMA ATIVIDADE NO BLOCO TUTOR DO MOODLE*/
				//o aluno respondeu a última atividade do curso
				message = "Olá, "+nome_aluno+", \n\n Parabéns!\n\n Você concluiu o curso! Espero que tenha sido proveitoso para você, e que tenha avançado nos seus conhecimentos sobre cálculo! \n\n Caso não tenha respondido a pesquisa, por favor, entre novamente no ambiente e responda uma pesquisa rápida sobre o curso para finalizar o teste prático do sistema.";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}else if(perfil.size() >= 2){
				//se entra aqui o aluno já teve uma(s) outra(s) atividade(s) realizada(s)
				if(perfil.get(1) == 1){
					//atividade anterior ele ficou no perfil básico
					perfil_anterior = 1;
					media_anterior = media.get(1);
				}else if(perfil.get(1) == 2){
					//atividade anterior ele ficou no perfil intermediário
					perfil_anterior = 2;
					media_anterior = media.get(1);
				}else if(perfil.get(1) == 3){
					//atividade anterior ele ficou no perfil avançado
					perfil_anterior = 3;
					media_anterior = media.get(1);
				}
				System.out.println("perfil anterior = "+ perfil_anterior+ " / media anterior = " + media_anterior);
				boolean aumentou = false;
				boolean diminuiu = false;
				boolean igual = false;
				if (perfil_anterior == perfil.get(0)){
					//se não mudou entre os dois perfis
					if(media_anterior == media.get(0)){
						igual = true;
					}else if(media_anterior < media.get(0)){
						aumentou = true;
					}else if(media_anterior > media.get(0)){
						diminuiu = true;
					}
					if(perfil_anterior == 1){
						//aluno se manteve no perfil básico
						if (igual){
							message = "Olá, "+nome_aluno+", \n\n Você manteve seu desempenho! Isso é bom, mas pode melhorar =) \n\n Que tal se esforçar um pouco mais no próximo conteúdo! Vamos tentar? Sei que você consegue!";
						}else if(aumentou){
							message = "Olá, "+nome_aluno+", \n\n Você melhorou seu desempenho! Isso é bom, mas pode melhorar ainda mais =) \n\n Vamos tentar no próximo conteúdo? Sei que você consegue!";
						}else if (diminuiu){
							message = "Olá, "+nome_aluno+", \n\n Você manteve seu desempenho, mas sua média ficou um pouco menor! \n\n Que tal se esforçar um pouco mais na próxima atividade! Vamos tentar? Sei que você consegue!";
						}
						this.insere_mensagem(nome_aluno, id, message, curso);
					}if(perfil_anterior == 2){
						//aluno se manteve no perfil intermediário
						if (igual){
							message = "Olá, "+nome_aluno+", \n\n Você manteve seu desempenho! Isso é bom, mas pode melhorar =) \n\n Que tal se esforçar um pouco mais! Vamos tentar no próximo tópico? Tenho certeza que você consegue!";
						}else if(aumentou){
							message = "Olá, "+nome_aluno+", \n\n Você melhorou seu desempenho! Isso é bom, mas ainda você consegue ir um pouco melhor =) \n\n Vamos tentar no próximo tema? Tenho certeza que você consegue!";
						}else if (diminuiu){
							message = "Olá, "+nome_aluno+", \n\n Você manteve sua média, mas dessa vez sua nota foi um pouco menor! \n\n Que tal se esforçar um pouco mais na próxima atividade! Vamos tentar? Tenho certeza que você consegue!";
						}
						this.insere_mensagem(nome_aluno, id, message, curso);
					}if(perfil_anterior == 3){
						//aluno se manteve no perfil avançado
						if (igual){
							message = "Olá, "+nome_aluno+", \n\n Parabéns! \n Você conseguiu se manter com um ótimo desempenho! Continue assim!";
						}else if(aumentou){
							message = "Olá, "+nome_aluno+", \n\n Parabéns! \n Você superou sua média anterior! Continue assim!";
						}else if (diminuiu){
							message = "Olá, "+nome_aluno+", \n\n Parabéns! \n Você conseguiu se manter com um ótimo desempenho!, mas dessa vez sua nota foi um pouco menor! \n\n Não tem problema! Você foi bem! Só não vale perder o foco, hein!";
						}
						this.insere_mensagem(nome_aluno, id, message, curso);
					}
				}else if (perfil_anterior < perfil.get(0)){
					//aluno aumentou o perfil
					if (lista_id_grade_item.get(0) == lista_id_grade_item.get(1)){
						//a atividade anterior e a nova são iguais
						message = "Olá, "+nome_aluno+", \n\n Você conseguiu se superar! Parabéns! Continue assim!";
						this.insere_mensagem(nome_aluno, id, message, curso);
					}else{
						//a atividade anterior e a nova são diferentes
						message = "Olá, "+nome_aluno+". \n\n Você conseguiu aumentar sua média! Parabéns! Continue assim!";
						this.insere_mensagem(nome_aluno, id, message, curso);
					}
				}else if (perfil_anterior > perfil.get(0)){
						message = "Olá, "+nome_aluno+", \n\n O seu desempenho teve uma pequena queda, mas não desanime! Você consegue se esforçar um pouco mais a próxima vez! Você tem capacidade. Vamos em frente!";
						this.insere_mensagem(nome_aluno, id, message, curso);
				}
		} else if (perfil.size() == 1){
			//é a primeira vez que o aluno fez alguma atividade no curso e teve uma nota maior que 2.5
			//ele ficou em um dos 3 perfis (básico, intermediário ou avançado)
			//verificar o perfil em que ele ficou
			if (perfil.get(0) == 1){
				//O primeiro perfil do aluno foi básico
				message = "Olá, "+nome_aluno+", \n\n Você conseguiu uma nota para continuar avançando no curso. Parabéns! Na próxima atividade vamos tentar ir um pouco melhor? Você consegue!";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}else if (perfil.get(0) == 2){
				//O primeiro perfil do aluno foi intermediário
				message = "Olá, "+nome_aluno+", \n\n Você conseguiu uma nota para continuar avançando no curso. Parabéns! O que acha de tentar melhorar essa média na próxima atividade! Você consegue!";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}else if (perfil.get(0) == 3){
				//O primeiro perfil do aluno foi avançado
				message = "Olá, "+nome_aluno+", \n\n Você conseguiu uma nota para continuar avançando no curso. E sua nota foi muito boa! Parabéns! Continue assim!";
				this.insere_mensagem(nome_aluno, id, message, curso);
			}
		}		
	}
	System.out.println(" message para o aluno = "+message);
	this.fecharConexao();
	}
	
	int verifica_availability_manda_inserir_grupo(String id, String curso, String id_module_atividade, String id_module_atividade_perfil)throws SQLException, ClassNotFoundException {
	//	this.conexaoBD("verifica_availability_manda_inserir_grupo tutor");
		int id_grupo_existente_no_modulo = 0;
		int id_grupo = 0;
		boolean aluno_esta_no_grupo = true;
		String string = "SELECT availability FROM mdl_course_modules where id = "+ id_module_atividade; //pega campo availability do módulo para ver se ele já tem um grupo criado e relacionado a ele
		ResultSet rs_availability = this.select(string);
		rs_availability.next();
		if (rs_availability.getString("availability") != null){
			id_grupo_existente_no_modulo = this.getGroupId(rs_availability.getString("availability"));
			//o valor em availability é diferente de nulo, agente pega o id do grupo com o qual está relacionado esse grade item
		}else {
			//availability é null então, não tem id de grupo válido, fica como -1
			id_grupo_existente_no_modulo = -1;
		}
		if (id_grupo_existente_no_modulo != -1){
			//quer dizer que tem um grupo já criado para esse módulo
			aluno_esta_no_grupo = this.verifica_aluno_no_grupo(id, id_grupo_existente_no_modulo);
			if (!aluno_esta_no_grupo){				
				this.insere_aluno_grupo(id, id_grupo_existente_no_modulo);
			}
		}else{
			id_grupo = this.cria_grupo_module(id_module_atividade, id_module_atividade_perfil, "", curso);
			this.insere_aluno_grupo(id, id_grupo);
		}
		if (id_grupo_existente_no_modulo != -1){
			return id_grupo_existente_no_modulo;
		}
		this.fecharConexao();
		return id_grupo;
	}
	
	void insere_mensagem(String nome_aluno, String id, String message, String curso)throws SQLException, ClassNotFoundException, UnsupportedEncodingException {
		String string;
		long datahoje = System.currentTimeMillis() / 1000;
		message = message + " Clique no link e volte para o curso!! http://localhost/moodle/course/view.php?id=" + curso;
		String smallmessage = message;
		message = message +"\n\n\n --------------------------------------------------------------------- \n Este e-mail é a cópia de uma mensagem que foi enviada para você em Moodle. Clique http://localhost/moodle/message/index.php?user=" + id + "&id=2 para responder." ;
		string = "INSERT INTO mdl_message(useridfrom, useridto, subject, fullmessage, fullmessagehtml, smallmessage, timecreated) VALUES (14, " +id+ ", 'Nova mensagem do Tutor do Curso', '" +message+ "' , '', '" +smallmessage+ "', " +datahoje+" )";
		//System.out.println("consulta = "+ string);
		this.conexaoBD("insere_mensagem tutor");
		this.update(string);
		this.fecharConexao();
		string = "SELECT id FROM mdl_message WHERE useridfrom = '14' AND useridto = "+id+ " AND fullmessage = '"+message+"' AND timecreated = "+datahoje;
		//System.out.println("consulta = "+ string);
		ResultSet id_message = this.select(string);
		id_message.next();
		String unreadmessageid = id_message.getString("id");
		string = "INSERT INTO mdl_message_working(unreadmessageid, processorid) VALUES ("+unreadmessageid+", '4')";
		this.conexaoBD("insere_mensagem 2 tutor");
		this.update(string);
		this.fecharConexao();
		this.enviaemail(id, message);


		//Para indicar hay que refrescar la pagina del alumno
		//id -> indica el id del alumno
		//curso -> indica el id del curso
		String string2 = "INSERT INTO mdl_tutor_nota_perfil(id, curso_id, id_grade_item, aluno_id, nota, nro_calculo) VALUES ("+datahoje +"," + curso +", 0, " + id + ", 0, -1)";
		System.out.println( "Añadir refresco con nro_calculo -1" );
		this.conexaoBD("nro_calculo -1");
		this.update(string2);
		}
	
	void enviaemail(String id, String message)throws SQLException, ClassNotFoundException, UnsupportedEncodingException {
		Email email = new Email();
		//this.conexaoBD("enviaemail tutor");
		String string;
		string = "SELECT value FROM mdl_config WHERE name='smtpuser'";
		ResultSet rs_smtp_user = this.select(string);
		rs_smtp_user.next();
		String smtp_user = rs_smtp_user.getString("value");
		String email_before = this.devolve_before(smtp_user);
		string = "SELECT value FROM mdl_config WHERE name='smtppass'";
		ResultSet rs_smtp_pass = this.select(string);
		rs_smtp_pass.next();
		String smtp_pass = rs_smtp_pass.getString("value");
		string = "SELECT email FROM mdl_user WHERE id="+ id;
		ResultSet rs_email_user = this.select(string);
		rs_email_user.next();
		String[] to = new String[1];
		to[0] = rs_email_user.getString("email");
		String subject = "Nova mensagem de Tutor do Curso";
		email.sendFromGMail(email_before, smtp_pass, to, subject, message);
		System.out.println( "Correo electronico para enviar correos: " + email_before);
		this.fecharConexao();
	}
	
	String devolve_before(String smtp_user)throws SQLException, ClassNotFoundException{
		String[] email_before = new String[2];
		email_before = smtp_user.split("@");
		return email_before[0];
	}
	
	@OPERATION
	String verifica_atividade_anterior(String id_aluno, String curso) throws SQLException, ClassNotFoundException {
		//this.conexaoBD("verifica_atividade_anterior tutor");
		String string = "SELECT * FROM mdl_tutor_alunos_avaliados WHERE id_aluno = '"+ id_aluno + "' AND id_curso = '"+ curso +"' AND nro_calculo != '0' ORDER BY id DESC LIMIT 1";
		String id_gr_it_at_anterior = "";
		ResultSet rs_atividade_anterior = this.select(string);
		while(rs_atividade_anterior.next()){
			id_gr_it_at_anterior = rs_atividade_anterior.getString("id_grade_item");
		}
		this.fecharConexao();
		return id_gr_it_at_anterior;
	}
	
	@OPERATION
	public ArrayList<Associacao_id_module_perfil_id> devolve_atividades_para_mostrar(String curso, String id_module) throws SQLException, ClassNotFoundException{
		ArrayList<Associacao_id_module_perfil_id> lista_id_module_rec_at_para_mostrar = new ArrayList<Associacao_id_module_perfil_id>();
	//	this.conexaoBD("devolve_atividades_para_mostrar tutor");
		String string;
		string = "SELECT rec_ativ_id FROM mdl_tutor_dependencia WHERE curso_id = "+curso+" and pre_req_id="+ id_module;
		// pega as atividades que têm como pre requisito a última atividade feita pelo alunos, para serem mostradas a seguir para o aluno
		ResultSet rec_at_ids = this.select(string);
		while (rec_at_ids.next()){
			if (!lista_id_module_rec_at_para_mostrar.contains(rec_at_ids.getString("rec_ativ_id"))){
				string = "SELECT perfil_id FROM mdl_tutor_rec_at_perfil WHERE curso_id = "+curso+" and rec_ativ_id="+ rec_at_ids.getString("rec_ativ_id");
				ResultSet perfil_id = this.select(string);
				perfil_id.next();
				Associacao_id_module_perfil_id id_mod_perfil_id = new Associacao_id_module_perfil_id();
				id_mod_perfil_id.setPerfilId(perfil_id.getString("perfil_id"));
				id_mod_perfil_id.setIdModule(rec_at_ids.getString("rec_ativ_id"));				
				lista_id_module_rec_at_para_mostrar.add(id_mod_perfil_id);
				//lista com o id_module e perfil (1, 2, 3 ou 4) da atividade que deve ser mostrada para os alunos
			}
		}
		this.fecharConexao();
		return lista_id_module_rec_at_para_mostrar;
	}
	
	@OPERATION /*ESTE MÉTODO TEM UMA VERSÃO NO BEDEL_ATC*/
	String converte_id_grade_item_em_id_module(String gradeItem, String curso) throws SQLException, ClassNotFoundException {
		System.out.println("Entrei em converte_id_grade_item_em_id_module 10");
		ArrayList<Associacao> lista_modules = this.lista_modules();
	//	this.conexaoBD("converte_id_grade_item_em_id_module tutor");
		String string;
		string = "SELECT * FROM mdl_grade_items WHERE courseid=" + curso + " AND id=" + gradeItem;
		ResultSet tabela_item_instance = this.select(string);
		String id_module = "";
		String item_instance = "";
		String item_module = "";
		String id_item_module = "";
		while (tabela_item_instance.next()) {
			item_instance = tabela_item_instance.getString("iteminstance");
			item_module = tabela_item_instance.getString("itemmodule");
		}
		for (int i = 0; i < lista_modules.size(); i++) {
			if (lista_modules.get(i).getItemmodule().equals(item_module)) {
				id_item_module = lista_modules.get(i).getId();
			}
		}
		string = "SELECT id FROM mdl_course_modules WHERE course=" + curso + " AND module=" + id_item_module + " AND instance=" + item_instance;
		ResultSet id_course_modules = this.select(string);
		while (id_course_modules.next()) {
			id_module = id_course_modules.getString("id");
		}
		this.fecharConexao();
		return id_module;
	}
	
	public ArrayList<Associacao> lista_modules() throws SQLException, ClassNotFoundException { /*ESTE MÉTODO TEM UMA VERSÃO NO BEDEL_ATC*/
		System.out.println("Entrei em lista_modules 19");
	//	this.conexaoBD("lista_modules tutor");
		String string;
		string = "SELECT * FROM mdl_modules"; // seleciona a tabela modules
		ResultSet modules = this.select(string);
		ArrayList<Associacao> id_modules = new ArrayList<Associacao>();
		while (modules.next()) {
			Associacao ass_modules = new Associacao();
			ass_modules.setId(modules.getString("id")); // id = id dos m�dulos
			ass_modules.setItemmodule(modules.getString("name")); // itemmodule = nome dos módulos
			id_modules.add(ass_modules); // lista com id e nome de modules
		}
		this.fecharConexao();
		return id_modules;
	}
	
	@OPERATION /*ESTE MÉTODO TEM UMA VERSÃO NO BEDEL_ATC*/
	int cria_grupo_module(String idModule, String perfilId, String adicional, String curso) throws SQLException, ClassNotFoundException {
		int idCourse = Integer.parseInt(curso);
		System.out.println("Entrei em cria_grupo_module 14");
		//cria grupo em um modulo com perfil específico // 1 = básico // 2 = médio // 3 = avançado // 4 = geral
		//this.conexaoBD("cria_grupo_module tutor");
		String string;
		//ArrayList<String> lista_id_ultimos_grupos = new ArrayList<String>();
		int id_grupo_existente_no_modulo = 0;
		string = "SELECT availability FROM mdl_course_modules where id = "+ idModule; //pega campo availability do módulo para ver se ele já tem um grupo criado e relacionado a ele
		ResultSet rs_availability = this.select(string);
		rs_availability.next();
		if (rs_availability.getString("availability") != null){
			id_grupo_existente_no_modulo = this.getGroupId(rs_availability.getString("availability"));
			//o valor em availability é diferente de nulo, agente pega o id do grupo com o qual está relacionado esse grade item
		}else {
			//availability é null então, não tem id de grupo válido, fica como -1
			id_grupo_existente_no_modulo = -1;
		}
		if (id_grupo_existente_no_modulo != -1){
		//já existe um grupo relacionado e criado nesse módulo.
			return id_grupo_existente_no_modulo;
		}else if (id_grupo_existente_no_modulo == -1){
		//quer dizer não há nenhum grupo relacionado com esse módulo, então, cria o grupo.
		WebServiceMoodle ws = new WebServiceMoodle();
		try{
			if (perfilId.equals("1")){
				if (adicional.equals("adicional")){
					ws.cria_grupos("Adaptação adicional B " + idModule, idCourse);
				}else{
					ws.cria_grupos("Adaptação B " + idModule, idCourse);	
				}
			}if (perfilId.equals("2")){
				ws.cria_grupos("Adaptação M " + idModule, idCourse);
			}if (perfilId.equals("3")){
				ws.cria_grupos("Adaptação A " + idModule, idCourse);
			}if (perfilId.equals("4")){
				ws.cria_grupos("Adaptação G " + idModule, idCourse);
			}
		//	this.conexaoBD("cria_grupo_module 2 tutor");
			string = "SELECT * FROM mdl_groups WHERE courseid = "+idCourse+" ORDER BY id DESC LIMIT 1";
			ResultSet rs_id_grupo = this.select(string);
			rs_id_grupo.next();
			id_grupo_existente_no_modulo = rs_id_grupo.getInt("id");
		}catch(Exception e){
				System.out.println("criei grupo WS dentro da exception "+ e.getMessage()+ " para o curso "+ idCourse);
		}			
		System.out.println("criei grupo NOVO " + id_grupo_existente_no_modulo + " para o curso "+ idCourse);
		}
		this.fecharConexao();
		return id_grupo_existente_no_modulo;
	}
	
	@OPERATION 
	boolean verifica_aluno_no_grupo(String id, int id_grupo_existente_no_modulo) throws SQLException, ClassNotFoundException {
		boolean aluno_esta_no_grupo = false;
	//	this.conexaoBD("verifica_aluno_no_grupo tutor");
		String string = "SELECT * FROM mdl_groups_members WHERE groupid = "+ id_grupo_existente_no_modulo + " AND userid = "+ id;
		ResultSet rs_membro = this.select(string);
		if (rs_membro.isBeforeFirst()){
			//se existe pelo menos um valor no resultado quer dizer que o aluno está no grupo
			aluno_esta_no_grupo = true;
		}
		this.fecharConexao();
		return aluno_esta_no_grupo;
	}
	
	@OPERATION /*ESTE MÉTODO TEM UMA VERSÃO NO BEDEL_ATC*/
	void insere_aluno_grupo(String id_aluno, int id_grupo) throws SQLException, ClassNotFoundException {
		System.out.println("Entrei em insere_aluno_grupo 15");
		long datahoje = System.currentTimeMillis() / 1000;
	//	this.conexaoBD("insere_aluno_grupo tutor");
		String string;
		string = "INSERT INTO mdl_groups_members(groupid,userid,timeadded) VALUES (" + id_grupo + ", " + id_aluno + " , "+ datahoje +" )";
		this.conexaoBD("insere_aluno_grupo tutor");
		this.update(string);
		this.fecharConexao();
		System.out.println("Inseri aluno "+ id_aluno+ " no grupo "+ id_grupo);
	}
	
	public static int getGroupId(String jsonFromDB) { /*ESTE MÉTODO TEM UMA VERSÃO NO BEDEL_ATC*/
		System.out.println("Entrei em getGroupId 20");
		//devolve o id do grupo que está informado na string do campo availability da tabela mdl_course_modules
		if (jsonFromDB.contains("\"type\":\"group\"")) {
			int idIndex = jsonFromDB.indexOf("\"id\":") + 5;
			int endIndex = jsonFromDB.indexOf("}");
			String stringId = jsonFromDB.substring(idIndex, endIndex);
			return Integer.parseInt(stringId);
		}
		return -1;
	}
	
	
}
