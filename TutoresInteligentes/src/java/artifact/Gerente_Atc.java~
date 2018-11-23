// CArtAgO artifact code for project moodle

package artifact;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.LinkedList;

import abs.Course;
import abs.Moodle;
import abs.User;
import cartago.OPERATION;
import cartago.ObsProperty;
import cartago.OpFeedbackParam;

public class Gerente_Atc extends db_art {

	Moodle moodle = new Moodle();

	public void init(int initialValue) {
		System.out.println("ENTREI NO INIT DO Gerente_Atc.java");
		defineObsProperty("count", initialValue);
		super.init(initialValue);
		this.iniciaCursos();
		this.iniciaAlunos();
	}
	
	public boolean alunoJaProcessado(int idAluno){
		System.out.println("ENTREI NO ALUNOJAPROCESSADO DO Gerente_Atc.java");
		try{
			ResultSet rs = super.select("select count(id_aluno) from mdl_tutor_tutor_aluno where id_aluno =" + idAluno);
		rs.next();
		int result = rs.getInt("count(id_aluno)");
		
		return result > 0;
		
		} catch(SQLException e){
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		}
		return false;
	}

	private LinkedList<User> iniciaAlunos() {
		LinkedList<User> alunosSemAgente = new LinkedList<User>();
		try {
			ResultSet users = super.select("SELECT rs.userid, c.id FROM mdl_role_assignments rs INNER JOIN mdl_context e ON rs.contextid = e.id INNER JOIN mdl_course c ON c.id = e.instanceid WHERE e.contextlevel = 50 AND rs.roleid = 5 AND c.id in (select id_curso from mdl_tutor_bedel_curso)");
			while (users.next()) {
				int userId = users.getInt("userid");
				int courseId = users.getInt("id");

				moodle.addUser(userId, courseId);
				User user = new User(userId);
				user.addCurso(courseId);

			}
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return alunosSemAgente;
	}

	private LinkedList<Course> iniciaCursos() {
		LinkedList<Course> cursosSemAgente = new LinkedList<Course>();
		try {
			ResultSet cursos = super
					.select("select c.instanceid from mdl_context as c inner join mdl_block_instances as i ON c.contextlevel = '50' and i.blockname = 'tutor' AND (c.path like concat('%/', i.parentcontextid, '/%') or c.path like concat('%/', i.parentcontextid)) where c.instanceid not in (select id_curso from mdl_tutor_bedel_curso)");

			while (cursos.next()) {
				int i = cursos.getInt(1);
				cursosSemAgente.add(new Course(i));
				moodle.addCourse(i);
			}
		} catch (SQLException e) {
		} catch (ClassNotFoundException e) {
		}
		return cursosSemAgente;
	}

	private void iniciaTabelas() {
		System.out.println("Entrei em inicia tabelas");
		try {
			this.connectBD();
			System.out.println("Entrei no try em inicia tabelas");
			String string = "DELETE FROM mdl_tutor_bedel_curso WHERE id_curso = '12'";
			this.update(string);
			//conn.createStatement().executeUpdate("truncate table mdl_tutor_bedel_curso");
			conn.createStatement().executeUpdate("truncate table mdl_tutor_tutor_aluno");
		} catch (SQLException e) {
			System.out.println("Entrei no catch em inicia tabelas");
			e.printStackTrace();
		} catch (Exception e) {
			//e.printStackTrace();
			System.out.println("erro inicia tabelas"+ e);
			System.out.println("Entrei no catch 2 em inicia tabelas");

		}

	}

	@OPERATION
	public void inicia() {
		System.out.println("Preparing DB tables...");
		iniciaTabelas();
		iniciaCursos();
		System.out.println("Courses started");
		iniciaAlunos();
		System.out.println("Users started");
		System.out.println("Artifact started...");
		
	}

	@OPERATION
	void inc() {
		System.out.println("ENTREI NO inc DO Gerente_Atc.java");
		ObsProperty prop = getObsProperty("count");
		prop.updateValue(prop.intValue() + 1);
		signal("tick");
	}

	@OPERATION
	public void verificaNovos() {
		LinkedList<User> alunos = iniciaAlunos();
		LinkedList<Course> cursos = iniciaCursos();
		for (User u : alunos) {
			moodle.addUser(u.getId(),0);
		}
		for (Course c : cursos) {
			moodle.addCourse(c.getId());
		}
	}

	@OPERATION
	public void getNaIdUser(OpFeedbackParam<Integer> id,
			OpFeedbackParam<String> nome, OpFeedbackParam<Boolean> bool) {
		try {
			User u = moodle.getNextNaUser(bool);
			u.setAgent();
			id.set(u.getId());
			nome.set(u.getUserName());
		} catch (Exception e) {
			bool.set(false);
		}
	}

	@OPERATION
	public void getNaIdCourse(OpFeedbackParam<Integer> id,
			OpFeedbackParam<String> nome, OpFeedbackParam<Boolean> bool) {
		try {
			Course c = moodle.getNextNaCourse(bool);
			c.setAgent();
			id.set(c.getId());
			nome.set(c.getCourseName());
		} catch (Exception e) {
			bool.set(false);
		}
	}

	

}
