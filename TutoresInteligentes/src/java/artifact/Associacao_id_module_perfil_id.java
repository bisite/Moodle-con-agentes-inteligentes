package artifact;

import java.util.ArrayList;

public class Associacao_id_module_perfil_id {
	
	String id_module;
	String perfil_id;

	Associacao_id_module_perfil_id(){
		id_module = "";
		perfil_id = "";	
	}

	public String getIdModule() {
		return id_module;
	}

	public void setIdModule(String id_module) {
		this.id_module = id_module;
	}

	public String getPerfilId() {
		return perfil_id;
	}

	public void setPerfilId(String perfil_id) {
		this.perfil_id = perfil_id;
	}

}
