import threading
import Queue
import json
import SocketServer
import socket
import ipaddress
from time import sleep

from sh import ping, ping6, traceroute, mtr

try:
	import lepconf
except ImportError, err:
	print "Couldn't import the config."
	raise err

ping = ping.bake(c=4, _ok_code=[0,1])
ping6 = ping6.bake(c=4, _ok_code=[0,1])

traceroute4 = traceroute.bake("-4")
traceroute6 = traceroute.bake("-6")

mtr4 = mtr.bake("-4", "--report", "--report-wide")
mtr6 = mtr.bake("-6", "--report", "--report-wide")

queue = Queue.Queue()

class QueryThread(threading.Thread):
	def __init__(self, queue):
		threading.Thread.__init__(self)
		self.queue = queue
	
	def run(self):
		while True:
			query = self.queue.get()
			out = None
			if query.type == "ping":
				out = ping(query.query)
			elif query.type == "ping6":
				out = ping6(query.query)
			elif query.type == "trace":
				out = traceroute4(query.query)
			elif query.type == "trace6":
				out = traceroute6(query.query)
			elif query.type == "mtr":
				out = mtr4(query.query)
			elif query.type == "mtr6":
				out = mtr6(query.query)
			
			if out:
				print "Processed query",str(query.id)
				send_response(query, out)
			else:
				print "Unable to process query",str(query.id),"- invalid type",type

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
		
		if not obj['auth'] == lepconf.auth:
			put(self.request, 'denied')
		elif not valid_query(obj['query']):
			put(self.request, 'invalid_query')
		else:
			queue.put(Query(obj['id'], obj['serverid'], obj['query'], obj['type']))

class ThreadedTCPServer(SocketServer.ThreadingMixIn, SocketServer.TCPServer):
	pass

def valid_query(query):
	try:
		if ipaddress.ip_address(query):
			return 1
	except ValueError:
		pass
	try:
		socket.gethostbyname(query)
		return 1
	except socket.error:
		return 0

def send_response(query, response):
	sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
	sock.connect(lepconf.remote)
	try:
		res = {
			"auth" : lepconf.auth,
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
