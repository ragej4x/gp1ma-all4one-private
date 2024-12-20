import socketserver
import socket
import os
import sys
import threading
import time
import json
import random
import base64


class MyHandler(socketserver.BaseRequestHandler):
    def handle(self):
        self.data = self.request.recv(1024).strip()
        print("{} wrote:".format(self.client_address[0]))
        print(self.data)
        self.request.sendall(self.data.upper())

if __name__ == "__main__":
    HOST, PORT = "localhost", 9999
    server = socketserver.ThreadingTCPServer((HOST, PORT), MyHandler)
    server.serve_forever()
    