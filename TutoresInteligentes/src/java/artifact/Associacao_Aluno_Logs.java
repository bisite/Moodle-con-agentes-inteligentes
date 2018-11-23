package artifact;

public class Associacao_Aluno_Logs {
	
	String id_aluno;
	int count_logs;
	
	public Associacao_Aluno_Logs() {
		id_aluno = "";
		count_logs = 0;
	}

	public String getId_aluno() {
		return id_aluno;
	}

	public void setId_aluno(String id_aluno) {
		this.id_aluno = id_aluno;
	}

	public int getCount_logs() {
		return count_logs;
	}

	public void setCount_logs(int count_logs) {
		this.count_logs = count_logs;
	}

}
