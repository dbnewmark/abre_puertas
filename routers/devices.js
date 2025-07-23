const express = require('express');
const router = express.Router();
const Device = require('../models/Device');
const User = require('../models/User');
const authenticate = require('../middleware/authenticate');

// Alta de dispositivo ESP32
router.post('/register', authenticate, async (req, res) => {
  try {
    const { chipId, alias } = req.body;
    const userId = req.user.id;

    if (!chipId || typeof chipId !== 'string') {
      return res.status(400).json({ error: 'chipId es requerido.' });
    }

    let device = await Device.findOne({ chipId });
    if (device) {
      if (device.owner.toString() === userId) {
        return res.status(200).json({ message: 'Dispositivo ya registrado por este usuario.', device });
      } else {
        return res.status(403).json({ error: 'Dispositivo ya registrado por otro usuario.' });
      }
    }

    device = new Device({
      chipId,
      alias: alias || `Puerta ${chipId}`,
      owner: userId,
      authorizedUsers: [userId],
      isOnline: false,
      lastSeen: null,
      createdAt: new Date(),
    });
    await device.save();

    await User.findByIdAndUpdate(userId, { $push: { devices: device._id } });

    res.status(201).json({ message: 'Dispositivo registrado exitosamente.', device });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Error interno del servidor.' });
  }
});

module.exports = router;