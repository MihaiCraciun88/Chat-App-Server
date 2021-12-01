<!doctype html>
<html lang="en">
  <head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<title>Chat App Server</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<style>
body {background: #333;color:#fff;}
.message {background:#6c757d;margin:0 0 5px 0;padding:10px;border-radius:10px;clear:both;float:left;color:#fff;}
.self-message {background:#0d6efd;float:right;}
#history {overflow:hidden;max-height:300px;}
</style>
</head>
<body>
<div class="container py-4">
    <div id="history"></div>
    <form>
        <div class="mb-3">
            <label for="mesage" class="form-label">Mesaj</label>
            <textarea id="message" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <button id="send" type="button" class="btn btn-danger">Send</button>
        </div>
    </form>
</div>

<script src="js/autobahn.js"></script>
<script>
window.onload = function() {
    var conn = new ab.Session('ws://localhost:8080',
        function() {
            var wsChatId = localStorage.getItem('wsChatId') || conn.sessionid();
            var message = document.getElementById('message');
            var history = document.getElementById('history');

            localStorage.setItem('wsChatId', wsChatId);

            document.getElementById('send').onclick = function() {
                var data = {
                    'wsChatId': wsChatId,
                    'message': message.value
                };
                conn.publish(wsChatId, JSON.stringify(data));
                message.value = '';
            };

            conn.subscribe(wsChatId, function(topic, data) {
                let div = document.createElement('div');
                div.className = 'message' + (data.wsChatId === wsChatId ? ' self-message' : '');
                div.innerHTML = data.message.replace(/\n/, '<br>');
                history.append(div);
            });
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );
}
</script>

</body>
</html>