console.log("loading dependencies");
const Discord = require('discord.io');
const https = require('https');
const auth = require('./auth.json');

console.log("initialize");
// Initialize Discord Bot
const bot = new Discord.Client({
    token: auth.token,
    autorun: true
});

function handleCards(data){
	var magicChannel = "enter yours";
	var terrariaChannel = "enter yours";
	
	// var debugChannel = "enter yours";
	// magicChannel = debugChannel;
	// terrariaChannel = debugChannel;
	
	console.log('handling cards...');
	var magic = data.magic;
	var terraria = data.terraria;
	var id = setInterval(()=>{
		var todo = (magic.length + terraria.length);
		console.log('todo: ' + todo);
		if (todo == 0){
			console.log('done, stopping queue');
			clearInterval(id);
		}
		displayCard(magic.pop(), magicChannel);
		setTimeout(()=>{
			displayCard(terraria.pop(), terrariaChannel);
		}, 1000); // this just looks cooler in the console
	}, 2250); // 1 per second max + headroom
}

function displayCard(card, channel){
	if (card == null) {
		return;
	}
	console.log('sending one card to ' + channel);
	
	var spam = '';
	spam += card.title;
	spam += '\r\n';
	spam += card.link;
	
	bot.sendMessage({
		to: channel,
		message: spam
	});
}

console.log("starting cron job");
function runCron(){
	console.log("Polling rss...");
	const options = {
		hostname: auth.hostname,
		port: auth.port,
		path: '/?id=' + auth.id,
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Content-Length': 0
		}
	}
	
	var data = '';
	// Request received
	const request = https.request(options, res => {
		res.on('data', chunk => {
			data += chunk
		});
		res.on('end', ()=> {
			// console.log(JSON.parse(data));
			handleCards(JSON.parse(data));
		});
		
	});
	// Error
	request.on('error', error => {
		console.log(error);
	})
	request.end();
}
setTimeout(()=>{
	runCron();
}, 100);
setInterval(
	function(){ 
		runCron();
	}, 
	1000*60*30
);

// Automatically reconnect if the bot disconnects due to inactivity
bot.on('disconnect', function(erMsg, code) {
    console.log('----- Bot disconnected from Discord with code', code, 'for reason:', erMsg, '-----');
    bot.connect();
});
