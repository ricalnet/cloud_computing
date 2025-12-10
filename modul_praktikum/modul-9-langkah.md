### **Langkah 1: Membuat Instance EC2 Ubuntu**
1. Login ke **AWS Console** dan buka layanan **EC2**.
2. Klik **"Launch Instance"**.
3. Beri nama instance, misalnya: `Monitoring-Server`.
4. Pilih **AMI Ubuntu 24.04 LTS**.
5. Pilih tipe instance **t3.small** (gratis untuk tier gratis).
6. Buat atau pilih **key pair** untuk SSH.
7. Pada **Network Settings**, klik **"Edit"** dan buka port berikut:
   - **Port 22** (SSH) – akses terbatas ke IP Anda.
   - **Port 9090** (Prometheus) – akses publik.
   - **Port 9100** (Node Exporter) – akses publik.
   - **Port 3000** (Grafana) – akses publik.
8. Klik **"Launch Instance"**.

---

### **Langkah 2: Login ke EC2 via SSH**
1. Setelah instance **running**, salin **Public IPv4 Address**.
2. Gunakan terminal (Linux/Mac) atau PowerShell/CMD (Windows) untuk SSH:
   ```bash
   ssh -i "kunci.pem" ubuntu@<IP-EC2>
   ```

---

### **Langkah 3: Install Docker Engine**

> **Referensi:** [Install Docker Engine on Ubuntu](https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository)

1. Set up Docker's apt repository:
   ```bash
   # Add Docker's official GPG key:
   sudo apt update
   sudo apt install ca-certificates curl
   sudo install -m 0755 -d /etc/apt/keyrings
   sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
   sudo chmod a+r /etc/apt/keyrings/docker.asc

   # Add the repository to Apt sources:
   sudo tee /etc/apt/sources.list.d/docker.sources <<EOF
   Types: deb
   URIs: https://download.docker.com/linux/ubuntu
   Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
   Components: stable
   Signed-By: /etc/apt/keyrings/docker.asc
   EOF

   sudo apt update
   ```   

2. Install Docker:
   ```bash
   sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
   ```
3. Verifikasi instalasi:
   ```bash
   sudo docker --version
   ```
4. Tambahkan user ke grup Docker (opsional):
   ```bash
   sudo usermod -aG docker $USER
   ```
   Lalu logout dan login ulang SSH.

---

### **Langkah 4: Buat Folder Project**
```bash
mkdir ~/monitoring-project
cd ~/monitoring-project
```

---

### **Langkah 5: Buat File docker-compose.yml**
1. Buat file `docker-compose.yml`:
   ```bash
   nano docker-compose.yml
   ```
2. Salin konten berikut:
   ```yaml
   services:
     prometheus:
       image: prom/prometheus:latest
       container_name: prometheus
       ports:
         - "9090:9090"
       volumes:
         - ./prometheus.yml:/etc/prometheus/prometheus.yml
       restart: always

     node-exporter:
       image: prom/node-exporter:latest
       container_name: node-exporter
       ports:
         - "9100:9100"
       restart: always

     grafana:
       image: grafana/grafana-oss:latest
       container_name: grafana
       ports:
         - "3000:3000"
       restart: always
   ```
3. Simpan: **Ctrl + X**, lalu **Y**, lalu **Enter**.

---

### **Langkah 6: Buat File prometheus.yml**
1. Buat file `prometheus.yml`:
   ```bash
   nano prometheus.yml
   ```
2. Salin konfigurasi berikut:
   ```yaml
   global:
     scrape_interval: 15s

   scrape_configs:
     - job_name: 'prometheus'
       static_configs:
         - targets: ['prometheus:9090']
     - job_name: 'node-exporter'
       static_configs:
         - targets: ['node-exporter:9100']
   ```
3. Simpan: **Ctrl + X**, lalu **Y**, lalu **Enter**.

---

### **Langkah 7: Jalankan Container dengan Docker Compose**
1. Dari folder `monitoring-project`, jalankan:
   ```bash
   sudo docker compose up -d
   ```
2. Cek status container:
   ```bash
   sudo docker ps
   ```
   Pastikan ketiga container **Prometheus**, **Node Exporter**, dan **Grafana** berstatus **Up**.

---

### **Langkah 8: Verifikasi Prometheus Targets**
1. Buka browser dan akses:
   ```
   http://<IP-EC2>:9090/targets
   ```
2. Pastikan muncul **2 target** dengan status **UP**:
   - `prometheus:9090`
   - `node-exporter:9100`

---

### **Langkah 9: Setup Grafana**
1. Buka browser dan akses:
   ```
   http://<IP-EC2>:3000
   ```
2. Login dengan kredensial default:
   - **Username:** `admin`
   - **Password:** `admin`
3. Ganti password jika diminta (disarankan untuk keamanan).
4. Tambahkan **Data Source**:
   - Klik ikon **Configuration (gear)** → **Data Sources** → **Add data source**.
   - Pilih **Prometheus**.
   - Pada **URL**, masukkan: `http://prometheus:9090`
   - Klik **Save & Test** → pastikan muncul pesan **"Data source is working"**.

---

### **Langkah 10: Buat Dashboard Grafana**
1. Klik **"+"** di sidebar → **Dashboard** → **Add new panel**.
2. Pilih **Data Source** = **Prometheus**.
3. Di **Query Editor**, masukkan metric:
   - **CPU Usage:**
     ```
     node_cpu_seconds_total
     ```
   - **Memory Available:**
     ```
     node_memory_MemAvailable_bytes
     ```
   - **Disk I/O:**
     ```
     node_disk_io_time_seconds_total
     ```
   - **Network Receive:**
     ```
     node_network_receive_bytes_total
     ```
4. Atur visualisasi (grafik, tabel, dll.) sesuai kebutuhan.
5. Klik **Apply** → **Save dashboard** (beri nama dan simpan).

---

### **Langkah 11: Selesai**
- Dashboard Grafana sudah aktif dan menampilkan metrik sistem dari Prometheus.
- Anda dapat menambahkan panel lain, atur alert, atau impor dashboard template dari komunitas Grafana.