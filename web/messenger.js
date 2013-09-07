var Messenger = function(target) {
	this.target = target;
	this.connection = null;
	
	this.connect = function (target) {
		this.connection = new WebSocket('ws://' + target);
		this.connection.onmessage = function(e) {
			this.onData(JSON.parse(e.data));
		}.bind(this);
		this.connection.onopen = this.onConnect.bind(this);
	};
	
	this.onConnect = function() {
		console.log("Connection established");
	};
	
	this.onData = function(data) {
		console.log("Data received", data);
		
		if (data[0] === 8) {
		    this.onEvent(data[1], data[2]);
		}
	};
	
	this.onDisconnect = function() {
		console.log("Disconnected");
	};
	
	this.send = function(data) {
		this.connection.send(JSON.stringify(data));
	};
	
	this.connect(target);
};
