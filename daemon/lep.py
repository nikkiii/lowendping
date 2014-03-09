import threading
import Queue
import json
import SocketServer
import socket
from time import sleep

from sh import ping, ping6, traceroute

try:
	import lepconf
except ImportError, err:
	print "Couldn't import the config."
	raise err

ping = ping.bake(c=4, _ok_code=[0,1])
ping6 = ping6.bake(c=4, _ok_code=[0,1])

queue = Queue.Queue()

class QueryThread(threading.Thread):
	def __init__(self, queue):
		threading.Thread.__init__(self)
		self.queue = queue
	
	def run(self):
		while True:
			query = self.queue.get()
			if query.type == "ping":
				out = ping(query.query)
			elif query.type == "ping6":
				out = ping6(query.query)
			elif query.type == "trace":
				out = traceroute(query.query)
			send_response(query, out)
			print "Processed query",str(query.id)

class Query:
	def __init__(self, id, serverid, query, type):
		self.id = id
		self.serverid = serverid
		self.query = query
		self.type = type

class ThreadedTCPRequestHandler(SocketServer.BaseRequestHandler):
	def handle(self):
		data = self.request.recv(1024)
		obj = json.loads(data)
		queue.put(Query(obj['id'], obj['serverid'], obj['query'], obj['type']))

class ThreadedTCPServer(SocketServer.ThreadingMixIn, SocketServer.TCPServer):
	pass

def send_response(query, response):
	sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	sock.connect(lepconf.remote)
	try:
		res = {
			"id" : query.id,
			"serverid" : query.serverid,
			"response" : str(response)
		}
		dump = json.dumps(res)
		
		put(sock, "POST %s HTTP/1.1" % lepconf.page)
		put(sock, "Host: %s" % lepconf.remote[0])
		put(sock, "Content-Type: application/json")
		put(sock, "Content-Length: %d" % len(dump))
		put(sock, "User-Agent: LowEndPing")
		put(sock, "Connection: close")
		put(sock, "")
		put(sock, dump)
		put(sock, "")
	finally:
		sock.close()

def put(sock, buf):
	if isinstance(buf, (list, tuple)):
		for scalar in buf: put(sock, scalar)
	else:
		sock.send(str(buf)+"\r\n")

if __name__ == "__main__":
	for i in range(lepconf.reqthreads):
		t = QueryThread(queue)
		t.setDaemon(True)
		t.start()
	
	HOST, PORT = "0.0.0.0", 12337

	server = ThreadedTCPServer((HOST, PORT), ThreadedTCPRequestHandler)

	# Start a thread with the server -- that thread will then start one
	# more thread for each request
	server_thread = threading.Thread(target=server.serve_forever)
	# Exit the server thread when the main thread terminates
	server_thread.daemon = True
	server_thread.start()
	
	print "Server loop running in thread:", server_thread.name
	
	while True:
		sleep(10);
