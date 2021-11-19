<div id="clients"></div>
<div id="history"></div>
mesaj
<textarea id="message"></textarea>
<input id="send" type="submit">

<script src="js/autobahn.js"></script>
<script>
window.onload = function() {
    var subscriptions = {};
    var conn = new ab.Session('ws://localhost:8080',
        function() {
            var wsChatId = '{{ env('PUSHER_ADMIN_KEY') }}';
            var clients = document.getElementById('clients');
            var message = document.getElementById('message');

            localStorage.setItem('wsChatId', wsChatId);

            document.getElementById('send').onclick = function() {
                var data = {
                    'wsChatId': 'admin',
                    'message': message.value
                };
                conn.publish(wsChatId, JSON.stringify(data));
                message.value = '';
            };

            function subscribe(conn, clientId) {
                conn.subscribe(clientId, function(topic, data) {
                    var history = document.getElementById('history');
                    let div = document.createElement('div');
                    div.className = 'message' + (data.wsChatId === 'admin' ? ' self-message' : '');
                    div.innerHTML = '#' + data.wsChatId + ': ' + data.message;
                    history.append(div);
                });
            }

            conn.subscribe(wsChatId, function(topic, data) {
                if (!(data.wsChatId in subscriptions)) {
                    subscriptions[data.wsChatId] = data;
                    console.log('subscribed', subscriptions);
                    // subscribe(conn, wsChatId, data.wsChatId);
                }
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