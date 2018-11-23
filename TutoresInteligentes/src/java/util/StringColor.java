package util;

import java.util.HashMap;

public class StringColor {

	private static HashMap<Color, String> colorMap = new HashMap<Color, String>();
	
	public static String getColor(Color c){
		String base =  "\u001B[";

		colorMap.put(Color.VERMELHO, base+ "31" + "m");
		colorMap.put(Color.VERDE, base+ "32" + "m");
		colorMap.put(Color.AMARELO, base+ "33" + "m");
		colorMap.put(Color.AZUL, base+ "34" + "m");
		colorMap.put(Color.MAGENTA, base+ "35" + "m");
		colorMap.put(Color.CYANO, base+ "36" + "m");
		colorMap.put(Color.BRANCO, base+ "37" + "m");
		
		return colorMap.get(c);
		
	}
	
	
	
}
