<?php

print '<script type="text/javascript">
		var type_simulation = document.getElementById("type_simulation");
		var tableau1 = document.getElementById("tableau1");

		//les input 
			brutInput = document.createElement("input");
			brutInput.name = "salaire_brut";
			brutInput.type = "text"; 

			netInput = document.createElement("input");
			netInput.name = "salaire_net"; 
			netInput.type = "text"; 

			coutInput = document.createElement("input");
			coutInput.name = "cout"; 
			coutInput.type = "text"; 

		//initialisation
		if(type_simulation.value == "salaire_brut"){
			ligne = tableau1.insertRow(-1);
			cell = ligne.insertCell(0);
			cell.innerHTML = "<label>Salaire brut</label>";
			cell = ligne.insertCell(1); 
			cell.innerHTML = brutInput;
			
		}else if(type_simulation.value=="salaire_net"){
			ligne = tableau1.insertRow(-1);
			cell = ligne.insertCell(0);
			cell.innerHTML = "<label>Salaire net</label>";
			cell = ligne.insertCell(1); 
			cell.innerHTML = netInput;

		}else{
			ligne = tableau1.insertRow(-1);
			cell = ligne.insertCell(0);
			cell.innerHTML = "<label>Cout</label>";
			cell = ligne.insertCell(1); 
			cell.innerHTML = coutInput;
		}

		type_simulation.addEventListener("change", function () {
			var ligne, cell;
			var nbLignes = tableau1.rows.length;
			if (nbLignes>8)
				{
					tableau1.deleteRow(nbLignes-1);
				}

			//alert(nbLignes);
    		

			if(type_simulation.value == "salaire_brut"){
			
				ligne = tableau1.insertRow(-1);
				cell = ligne.insertCell(0);
				cell.innerHTML = "<label>Salaire brut</label>";
				cell = ligne.insertCell(1); 
				cell.innerHTML = brutInput;
				
			}else if(type_simulation.value=="salaire_net"){
				ligne = tableau1.insertRow(-1);
				cell = ligne.insertCell(0);
				cell.innerHTML = "<label>Salaire net</label>";
				cell = ligne.insertCell(1); 
				cell.innerHTML = netInput;
	
			}else{
				ligne = tableau1.insertRow(-1);
				cell = ligne.insertCell(0);
				cell.innerHTML = "<label>Cout</label>";
				cell = ligne.insertCell(1); 
				cell.innerHTML = coutInput;
			}
		
		},
		false,
		);

	</script>';