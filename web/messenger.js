var Messenger = new Class({
    initialize: function(target){
    	this.target = target;
    	this.connect(target);
    },
    
	connect: function (target) {
		this.connection = new WebSocket('ws://' + target);
		this.connection.onmessage = function(e) {
			this.onData(JSON.parse(e.data));
		}.bind(this);
		this.connection.onopen = function() {
			this.onConnect();
		}.bind(this);
	},
	
	onConnect: function() {
		console.log("Connection established");
	},
	
	onData: function(data) {
		console.log("Data received", data);
		
		if (data[0] === 8) {
		    this.onEvent(data[1], data[2]);
		}
	},
	
	onDisconnect: function() {
		console.log("Disconnected");
	},
	
	send: function(data) {
		this.connection.send(JSON.stringify(data));
	}
});
