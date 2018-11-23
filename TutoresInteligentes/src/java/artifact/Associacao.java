package artifact;

public class Associacao {
	
	String id;
	String itemmodule;
	String instance;
	
	Associacao(){
		id = "";
		itemmodule = "";	
		instance = "";
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public String getItemmodule() {
		return itemmodule;
	}

	public void setItemmodule(String itemmodule) {
		this.itemmodule = itemmodule;
	}
	
	public String getInstance() {
		return instance;
	}

	public void setInstance(String instance) {
		this.instance = instance;
	}

}
