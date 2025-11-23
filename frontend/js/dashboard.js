// Verificar autenticación
const token = localStorage.getItem('token');
const user = JSON.parse(localStorage.getItem('user') || '{}');

if (!token) {
    window.location.href = 'login.html';
}

// Mostrar nombre de usuario
document.getElementById('userName').textContent = user.name || user.email;

// Cerrar sesión
document.getElementById('logoutBtn').addEventListener('click', async () => {
    try {
        await fetch(`${API_BASE_URL}/logout.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }
    
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'login.html';
});

// Registrar nuevo dispositivo
document.getElementById('registerDeviceForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const chipId = document.getElementById('chipId').value.trim();
    const alias = document.getElementById('alias').value.trim();
    const messageDiv = document.getElementById('register-message');
    
    try {
        const response = await fetch(`${API_BASE_URL}/register_device.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ chip_id: chipId, alias })
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.className = 'success-message';
            messageDiv.textContent = data.message;
            messageDiv.style.display = 'block';
            
            // Limpiar formulario
            document.getElementById('chipId').value = '';
            document.getElementById('alias').value = '';
            
            // Recargar lista de dispositivos
            setTimeout(() => {
                loadDevices();
                messageDiv.style.display = 'none';
            }, 2000);
        } else {
            messageDiv.className = 'error-message';
            messageDiv.textContent = data.error;
            messageDiv.style.display = 'block';
        }
    } catch (error) {
        messageDiv.className = 'error-message';
        messageDiv.textContent = 'Error de conexión con el servidor';
        messageDiv.style.display = 'block';
    }
});

// Cargar dispositivos
async function loadDevices() {
    const devicesList = document.getElementById('devicesList');
    
    try {
        const response = await fetch(`${API_BASE_URL}/list_devices.php`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.devices.length > 0) {
            devicesList.innerHTML = data.devices.map(device => createDeviceCard(device)).join('');
            
            // Agregar event listeners a los botones
            data.devices.forEach(device => {
                document.getElementById(`btn-on-${device.id}`).addEventListener('click', () => controlRelay(device.id, 'on'));
                document.getElementById(`btn-off-${device.id}`).addEventListener('click', () => controlRelay(device.id, 'off'));
                document.getElementById(`btn-toggle-${device.id}`).addEventListener('click', () => controlRelay(device.id, 'toggle'));
            });
        } else {
            devicesList.innerHTML = '<p class="loading">No tienes dispositivos registrados. Registra uno usando el formulario de arriba.</p>';
        }
    } catch (error) {
        devicesList.innerHTML = '<p class="error-message">Error al cargar dispositivos</p>';
    }
}

// Crear tarjeta de dispositivo
function createDeviceCard(device) {
    const isOnline = device.is_online == 1;
    const relayOn = device.relay_state == 1;
    const lastSeen = device.last_seen ? new Date(device.last_seen).toLocaleString() : 'Nunca';
    
    return `
        <div class="device-card">
            <h3>${device.alias || 'Dispositivo ' + device.chip_id}</h3>
            <div class="device-info">
                <strong>Chip ID:</strong> ${device.chip_id}
            </div>
            <div class="device-info">
                <strong>Estado:</strong>
                <span class="device-status ${isOnline ? 'status-online' : 'status-offline'}">
                    ${isOnline ? 'En línea' : 'Desconectado'}
                </span>
            </div>
            <div class="device-info">
                <strong>Última conexión:</strong> ${lastSeen}
            </div>
            <div class="relay-status">
                <span><strong>Relé:</strong> ${relayOn ? 'Encendido' : 'Apagado'}</span>
                <span class="relay-indicator">${relayOn ? '🟢' : '🔴'}</span>
            </div>
            <div class="device-controls">
                <button id="btn-on-${device.id}" class="btn btn-success">Encender</button>
                <button id="btn-off-${device.id}" class="btn btn-danger">Apagar</button>
                <button id="btn-toggle-${device.id}" class="btn btn-primary">Alternar</button>
            </div>
        </div>
    `;
}

// Controlar relé
async function controlRelay(deviceId, action) {
    try {
        const response = await fetch(`${API_BASE_URL}/control_relay.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ device_id: deviceId, action })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar dispositivos para actualizar el estado
            loadDevices();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (error) {
        alert('Error de conexión con el servidor');
    }
}

// Cargar dispositivos al inicio
loadDevices();

// Auto-refresh cada 10 segundos
setInterval(loadDevices, 10000);
