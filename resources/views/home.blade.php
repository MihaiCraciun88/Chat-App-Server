<div id="history"></div>
mesaj
<textarea id="message"></textarea>
<input id="send" type="submit">

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

            console.log(conn.sessionid());

            conn.subscribe(wsChatId, function(topic, data) {
                let div = document.createElement('div');
                div.className = 'message' + (data.wsChatId === wsChatId ? ' self-message' : '');
                div.innerHTML = data.message;
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

<style>
.message {background:#ccc;padding:10px;border-radius:10px;}
.self-message {background:#ccf;}
</style>