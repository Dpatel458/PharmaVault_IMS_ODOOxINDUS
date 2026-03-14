const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);

// Allow Symfony dashboard to connect via CORS
const io = new Server(server, {
  cors: {
    origin: '*', // In production, restrict to Symfony app URL
    methods: ['GET', 'POST']
  }
});

app.use(cors());
app.use(express.json());

// Main broadcast route for Symfony to hit
// e.g. POST http://localhost:3000/api/broadcast { "event": "stock_updated", "data": {...} }
app.post('/api/broadcast', (req, res) => {
    const { event, data } = req.body;
    
    if (!event) {
        return res.status(400).json({ error: 'Event name is required' });
    }

    console.log(`[HTTP] Received broadcast request for event: ${event}`);
    
    // Broadcast to all connected clients
    io.emit(event, data);
    
    res.json({ success: true, message: `Event ${event} broadcasted` });
});

// Socket.io connection handling
io.on('connection', (socket) => {
    console.log(`[Socket] Client connected: ${socket.id}`);
    
    socket.on('disconnect', () => {
        console.log(`[Socket] Client disconnected: ${socket.id}`);
    });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`IMS Node.js Microservice listening on port ${PORT}`);
});
