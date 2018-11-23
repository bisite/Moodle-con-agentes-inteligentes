package abs;


import java.util.ArrayList;
import java.util.List;
import java.util.NoSuchElementException;

import cartago.OpFeedbackParam;

public class Moodle {	
	private List<User> lUser;
	private List<Course> lCourse;

	public Moodle() {
		lUser = new ArrayList<User>();
		lCourse = new ArrayList<Course>();
	}

	public void addUser(int id, int courseId) {
		if (!this.userIsReady(id,courseId)) {
			lUser.add(new User(id));
		}
	}

	public void addCourse(int id) {
		if (!this.courseIsReady(id)) {
			lCourse.add(new Course(id));
		}
	}

	public boolean userIsReady(int id, int courseId) {
		boolean result = false;
		
		for(User u : lUser){
			if(u.getId() == id && !(result)){
				u.addCurso(courseId);
				result = true;	
			}
		}
		
		return (new User(id)).agentePronto() || result;
	}

	public boolean courseIsReady(int id) {
		boolean result = false;
		
		for(Course c : lCourse){
			if(c.getId() == id && !(result)){
				result = true;
				
			}
		}
		
		return (new Course(id)).agentePronto() || result;
	}


	public User getNextNaUser(OpFeedbackParam<Boolean> bool) throws NoSuchElementException{
		User a = null; 

		if(lUser.isEmpty())
			bool.set(false);
		else
			bool.set(true);
		
		if(bool.get()){
			a = lUser.remove(0);
		}else{
			throw new NoSuchElementException();
		}
			return a;
	}

	public Course getNextNaCourse(OpFeedbackParam<Boolean> bool) throws NoSuchElementException{
		Course a = null; 
		
		if(lCourse.isEmpty())
			bool.set(false);
		else
			bool.set(true);
		
		if(bool.get()){
			a = lCourse.remove(0);
		}else{
			throw new NoSuchElementException();
		}
			return a;
	}
}
