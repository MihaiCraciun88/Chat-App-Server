<!doctype html>
<html lang="en">
  <head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<title>Chat App Server</title>
<link href="{{ url('/') }}/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ url('/') }}/css/chatbox.css" rel="stylesheet">
<script src="{{ url('/') }}/js/chatbox.js"></script>
<script src="{{ url('/') }}/js/autobahn.js"></script>
<script>
class ChatboxWsDriver {
    init(chat) {
        var conn;
        chat.on('init', () => {
            conn = new ab.Session('ws://localhost:8080',
                () => {
                    conn.subscribe(chat.getUuid(), function(topic, data) {
                        console.log(topic, data);
                        if (chat.getUuid() === data.uuid) {
                            chat.addChat(data.text, data.date);
                        } else {
                            chat.addReply(data);
                        }
                    });
                },
                () => console.warn('WebSocket connection closed'),
                {'skipSubprotocolCheck': true}
            );
        });
        chat.on('send', (input) => {
            console.log(input);
            conn.publish(chat.config.uuid, JSON.stringify({
                'uuid': chat.config.uuid,
                'message': input,
            }));
        });
    }
}
const chat = new Chatbox(), driver = new ChatboxWsDriver();
chat.setDriver(driver);
</script>
<style>
body {background: #333;color:#fff;}
.message {background:#6c757d;margin:0 0 5px 0;padding:10px;border-radius:10px;clear:both;float:left;color:#fff;}
.self-message {background:#0d6efd;float:right;}
#history {overflow:hidden;max-height:300px;}
</style>
</head>
<body>
<div class="container py-4">
    
</div>
</body>
</html>