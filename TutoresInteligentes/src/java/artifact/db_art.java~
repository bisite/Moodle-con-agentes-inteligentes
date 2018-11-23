package artifact;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Map;

import cartago.OPERATION;
// CArtAgO artifact code for project moodleTutor

public class db_art extends BD_Artifact {
	protected Map<String, Integer> levelMap;
	private static db_art instance = null;
	String url = "jdbc:mysql://150.162.60.167:3306/moodle_cecilia";
    String login = "cecilia";
    String senha = "cecilia";
    
	public void setLevelMap() {
		levelMap.put("professor", 10);
		levelMap.put("aluno", 30);
	}
	
	public int countRS(ResultSet RS){
		int i = 0;
		try {
			while(RS.next()){
				i++;
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}
		return i;
	}
	
	protected db_art() {
	
	}

	/**
	 * @brief Is a operation, open DB connection
	 * @throws ClassNotFoundException
	 *             when "driver not found" error
	 * @throws SQLException
	 *             when "can't connect DB" error
	 */
	@OPERATION
	public void connectBD() {
		try {
			Class.forName("org.gjt.mm.mysql.Driver").newInstance();
			try {
		
				conn = super.conexaoBD();
			} catch (Exception ex) {
				System.out.println("\nErro no connection!");
			}
		} catch (Exception ex) {
			System.out.println("\nDriver nao pode ser carregado!");
		}
		
	}

	/**
	 * @brief "SELECT FROM WHERE" of SQL
	 * @alert EVER CLOSE THE CONNECTION
	 * @param select
	 * @param from
	 * @param where
	 * @return result of search
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	public ResultSet sfw(String select, String from, String where)
			throws SQLException, ClassNotFoundException {
		try {
			connectBD();
			String s = "SELECT " + select + " FROM " + from + " WHERE " + where;
			return super.select(s);
		} finally {
			//super.fecharConexao();
		}
	}

	/**
	 * @brief "SELECT FROM WHERE" of SQL
	 * @alert EVER CLOSE THE CONNECTION
	 * @param select
	 * @param from
	 * @param where
	 * @return result of search
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	public ResultSet sf(String select, String from) throws SQLException,
			ClassNotFoundException {
		try {
			connectBD();
			String s = "SELECT " + select + " FROM " + from;
			return super.select(s);
		} finally {
			//super.fecharConexao();
		}
	}
	
	public static db_art getInstance(){
		if(instance == null){
			instance = new db_art();
		}
		return instance;
	}
}
