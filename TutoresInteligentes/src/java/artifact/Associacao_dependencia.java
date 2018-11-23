package artifact;

import java.util.ArrayList;

public class Associacao_dependencia {
	
	String rec_ativ_id;
	ArrayList<String> pre_req_id;

	public Associacao_dependencia() {
		rec_ativ_id = "";
		pre_req_id = new ArrayList<String>();
	}

	public String getRec_ativ_id() {
		return rec_ativ_id;
	}

	public void setRec_ativ_id(String rec_ativ_id) {
		this.rec_ativ_id = rec_ativ_id;
	}

	public ArrayList<String> getPre_req_id() {
		return pre_req_id;
	}

	public void setPre_req_id(ArrayList<String> pre_req_id) {
		this.pre_req_id = pre_req_id;
	}
	
	public void inserePreReq (String valor_pre_req_id){
		this.pre_req_id.add(valor_pre_req_id);
	}


}
