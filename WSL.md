# Documentation for Windows Users: Setting Up Sepiina via WSL

This guide provides step-by-step instructions for Windows users to enable the Windows Subsystem for Linux (WSL), install Debian, and configure the Sepiina SQUID proxy server using our setup script.

---

## Step 1: Enable Windows Subsystem for Linux (WSL)

1. **Open PowerShell as Administrator**
   - Press `Win + S`, type `PowerShell`, right-click on "Windows PowerShell," and select "Run as Administrator."

2. **Enable WSL**
   Run the following command to enable WSL:
   ```powershell
   wsl --install
   ```
   - This command installs WSL and sets up Ubuntu as the default distribution.
   - If prompted, restart your computer.

3. **Optional: Check WSL Version**
   After restarting, ensure you're using WSL 2:
   ```powershell
   wsl --list --verbose
   ```
   - If the version is not WSL 2, upgrade it with:
     ```powershell
     wsl --set-default-version 2
     ```

---

## Step 2: Install Debian via WSL

1. **Download Debian from the Microsoft Store**
   - Open the Microsoft Store and search for "Debian."
   - Click "Get" and wait for the download and installation to complete.

2. **Launch Debian**
   - Once installed, open Debian from the Start Menu.
   - Complete the initial setup by creating a username and password for your Debian environment.

---

## Step 3: Prepare Debian for Sepiina Setup

1. **Update Package Lists**
   Run the following commands to update and upgrade your system:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Download the Sepiina Setup Script**
   Save the provided setup script to your Debian environment. For example, use the `curl` command:
   ```bash
   curl -O https://example.com/sepiina_setup.sh
   ```
   *(Replace `https://example.com/sepiina_setup.sh` with the actual URL for the script.)*

3. **Make the Script Executable**
   Grant execution permissions to the script:
   ```bash
   chmod +x sepiina_setup.sh
   ```

---

## Step 4: Run the Sepiina Setup Script

1. **Execute the Script**
   Run the setup script as root:
   ```bash
   sudo ./sepiina_setup.sh
   ```

2. **Follow On-Screen Instructions**
   The script will:
   - Install and configure the SQUID proxy server.
   - Set up a domain block list.
   - Rebrand the system as "Sepiina."

3. **Verify Installation**
   Once the script completes, verify that the SQUID proxy server is running:
   ```bash
   sudo systemctl status squid
   ```
   - If the status is "active (running)," the setup was successful!

---

## Step 5: Optional Configuration and Customization

1. **Update the Block List**
   Edit the block list file at `/etc/squid/blocked_domains.txt` to add or remove domains:
   ```bash
   sudo nano /etc/squid/blocked_domains.txt
   ```
   - Save changes and restart SQUID to apply updates:
     ```bash
     sudo systemctl restart squid
     ```

2. **Customize Logo**
   Replace the default logo at `/usr/share/pixmaps/sepiina_logo.png` with your own image if desired.

---

## Troubleshooting

- **Permission Denied Errors:** Ensure you are using `sudo` for commands requiring root privileges.
- **WSL Issues:** Refer to [Microsoft's WSL Documentation](https://learn.microsoft.com/en-us/windows/wsl/) for troubleshooting.
- **SQUID Configuration Errors:** Check the logs for more information:
  ```bash
  sudo tail -f /var/log/squid/access.log
  sudo tail -f /var/log/squid/cache.log
  ```

---

You have successfully installed Sepiina on your Windows system via WSL. Enjoy managing your custom proxy server!

