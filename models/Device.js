const mongoose = require('mongoose');

const DeviceSchema = new mongoose.Schema({
  chipId: { type: String, unique: true, required: true },
  alias: { type: String },
  owner: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  authorizedUsers: [{ type: mongoose.Schema.Types.ObjectId, ref: 'User' }],
  isOnline: { type: Boolean, default: false },
  lastSeen: { type: Date },
  createdAt: { type: Date, default: Date.now },
});

module.exports = mongoose.model('Device', DeviceSchema);