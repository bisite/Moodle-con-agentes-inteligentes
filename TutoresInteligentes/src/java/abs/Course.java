package abs;

import java.sql.ResultSet;
import java.sql.SQLException;

import artifact.db_art;

public class Course {
	private int id;
	db_art DB;
	
	public Course (int id) {
		this.id = id;
		DB = db_art.getInstance();
	}

	public int getId() {
		return id;
	}

	public String getCourseName(){
		ResultSet Rs;
		try {
			Rs = DB.sfw("fullname", "mdl_course", "id=" + id);
			Rs.next();
			return Rs.getString(1);
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return "ae";
	}
	
	@Override
	public boolean equals(Object id) {
		if (id instanceof Course) {
			return this.id == ((Course)id).getId();
		}
		else if(id instanceof Integer){
			return this.id == (Integer)id;
		}
		return false;
	}
	
	public void setAgent(){
		try {
			DB.update("INSERT INTO mdl_tutor_bedel_curso(id_curso) value("+id+");");
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
	}

	public boolean agentePronto() {
		boolean agentePronto = false;
		
		try {
			ResultSet bol= DB.select("SELECT id_curso FROM mdl_tutor_bedel_curso WHERE id_curso ="+this.id+";");
			
			if(bol.next()){
				agentePronto = true;
			}
		} catch (ClassNotFoundException e) {
			e.printStackTrace();
		} catch (SQLException e) {
			e.printStackTrace();
		}
		
		return agentePronto;
	}

}
