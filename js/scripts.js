$(document).ready( function() {
	// run jquery commands.
	$(".orderquantity").change( function() {
		//alert("hello");
		var products = $("#order-products > .itemline").length;
		
		var points = 0;
		for(var i = 0; i < products; i++){
			// iterate and calculate the total points. 
			points += parseInt($("#itempoints" + i).text()) * $(".orderqty" + i).val();
			
		}
		
		var rempoints = parseInt($(".totalpoints").text()) - points;
		
		if(rempoints < 0){
			$(".userremainingpoints").addClass("red");
		} else {
			$(".userremainingpoints").removeClass("red");
		}
		
		$(".ordertallypoints").html(points);
		$(".userremainingpoints").html(rempoints);
	});
	
	$("#mobilemenubutton").click( function() {
		// manage a button click. assume cannot be seen above 600px wide.
		
		var listitems = $("#menubar > li").length;
		
		
		
		if($("#menubar").height() > 0){
			$("#menubar").animate({height: "0px"}, 500);
		} else {
			if(listitems == 5){
				$("#menubar").animate({height: "230px"}, 500);
			} else {
				$("#menubar").animate({height: "275px"}, 500);
			}
		}
	});
	
	$(".percentboot").change( function() {
		// change the values of the form to display a new point value. 
		
		var theparent = $(this).closest(".order-line-content");
		
		// get the part number to determine what the value is. either DAKOTA15 or CSAFOOTWEAR10
		
		var adjustedpercent = 1.00;
		
		if(theparent.find(".partnumber").html() == "DAKOTA15"){
			// the value is 15%
			adjustedpercent = 0.85;
		 } else {
		 	adjustedpercent = 0.90;
		 }
		
		
		var adjustedvalue = adjustedpercent * parseFloat($(this).val());
		
		var pointvalue = parseInt(adjustedvalue * 10);
		
		theparent.find(".points").html(pointvalue + " Points");
		
		theparent.find(".retail").html("Retail: $" + parseFloat($(this).val()).toFixed(2));
		
		$(".orderquantity").change();
		//theparent.find(".points").css( "background-color", "red" );
		
		//theparent.css( "background-color", "green" );
		
	});
	
	
	$("#orderuser").change( function(){
		// change the display information when the user changes employee.
		
		$("#uchose").html(userPoints[$("#orderuser").val()][0]);
		$("#upoints").html(userPoints[$("#orderuser").val()][1]);
		$(".totalpoints").html(userPoints[$("#orderuser").val()][1]);
		$(".orderquantity").change();
	});
	if($("#orderuser").length){
		$("#orderuser").change();
	}
	
});