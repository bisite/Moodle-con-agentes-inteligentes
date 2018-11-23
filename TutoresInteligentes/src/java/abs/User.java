package abs;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.LinkedList;

import artifact.db_art;

public class User {
	private int id;
	private LinkedList<Integer> cursos = new LinkedList<Integer>();
	boolean agentePronto = false;
	db_art DB;

	public User(int id) {
		this.id = id;
		DB = db_art.getInstance();
	}

	public int getId() {
		return id;
	}

	public boolean equals(Object id) {
		if (id instanceof User) {
			return this.id == ((User) id).getId();
		} else if (id instanceof Integer) {
			return this.id == (Integer) id;
		}
		return false;
	}

	public String getUserName() {
		ResultSet Rs1;
		try {
			Rs1 = DB.sfw("firstname", "mdl_user", "id=" + id);
			Rs1.next();
			return Rs1.getString(1);

		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return null;
	}

	public void setAgent() {
		try {
			if (cursos.isEmpty())
				return;

			for (int id : cursos) {
				if(id == 0) return;
				DB.update("INSERT INTO mdl_tutor_tutor_aluno(id_aluno, id_curso) values("
						+ this.id + ", " + id + ");");
			//	System.out.println("Curso "+id+" adicionado ao aluno "+this.getId());
				cursos.remove(cursos.indexOf(id));
			}
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}

	public boolean agentePronto() {
		boolean agentePronto = false;

		try {
			ResultSet bol = DB
					.select("SELECT id_aluno FROM mdl_tutor_tutor_aluno WHERE id_aluno ="
							+ this.id + ";");

			if (bol.next()) {
				agentePronto = true;
			}
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}

		return agentePronto;
	}

	public void addCurso(int cId) {
		try {
			int myId = getId();
			
			ResultSet bol = DB
					.select("SELECT id_aluno FROM mdl_tutor_tutor_aluno WHERE id_curso ="+cId+" AND id_aluno = "+myId);
			if (bol.next()) {
				return;
			}
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}

		if (!cursos.contains(cId) || cId != 0)
			this.cursos.add(cId);
			this.setAgent();
	}
}
