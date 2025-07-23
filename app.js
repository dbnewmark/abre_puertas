require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const deviceRoutes = require('./routes/devices');

const app = express();
const port = process.env.PORT || 3000;

// Middlewares
app.use(express.json());

// Rutas
app.use('/devices', deviceRoutes);

// Conexión a MongoDB
mongoose.connect(process.env.MONGO_URI, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
})
.then(() => console.log('Conectado a MongoDB'))
.catch(err => console.error('Error de conexión MongoDB:', err));

// Inicio del servidor
app.listen(port, () => {
    console.log(`Servidor escuchando en puerto ${port}`);
});