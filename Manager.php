//This is a simple page to manage your squid proxy file --/etc/squid/blocked_domains.acl
// Note: Make sure the file is writeable for the www-data user or whichever your webserver name would be
<?php
// Initialize a message variable for user feedback.
$message = '';

// Define an associative array of categories with their corresponding flat file names.
// (You can update these flat files manually as needed.)
$categories = [
    'gambling'  => '/etc/squid/gambling_domains.acl',
    'sports'    => '/etc/squid/sports_domains.acl',
    'adult'     => '/etc/squid/adult_domains.acl',
    'games'     => '/etc/squid/games_domains.acl',
    'tobacco'   => '/etc/squid/tobacco_domains.acl',
    'firearms'  => '/etc/squid/firearms_domains.acl',
    'malicious' => '/etc/squid/malicious_domains.acl'
];

$aclFile = '/etc/squid/blocked_domains.acl';  // The main ACL file that Squid uses

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* --------------------------------------------
       1. Manual Domain Addition Form Processing
       -------------------------------------------- */
    if (isset($_POST['add_domain'])) {
        if (isset($_POST['domain']) && trim($_POST['domain']) !== '') {
            $domain = trim($_POST['domain']);

            // Validate the domain using FILTER_VALIDATE_DOMAIN with FILTER_FLAG_HOSTNAME (PHP 7+)
            if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                if ($fp = fopen($aclFile, 'a')) {
                    if (flock($fp, LOCK_EX)) {
                        if (fwrite($fp, $domain . "\n") !== false) {
                            $message .= "Domain added successfully. ";
                        } else {
                            $message .= "Error: Could not write to the file. ";
                        }
                        flock($fp, LOCK_UN);
                    } else {
                        $message .= "Error: Could not lock the file. ";
                    }
                    fclose($fp);
                } else {
                    $message .= "Error: File is not writable or does not exist. ";
                }
            } else {
                $message .= "Invalid domain. Please enter a valid domain (e.g., example.com). ";
            }
        } else {
            $message .= "Please enter a domain. ";
        }
    }

    /* --------------------------------------------
       2. Update List (Restart Squid) Form Processing
       -------------------------------------------- */
    if (isset($_POST['update_list'])) {
        // Execute the restart command via sudo.
        // Ensure your sudoers file allows the web server user to run this command without a password.
        $output = shell_exec('sudo /bin/systemctl restart squid.service 2>&1');
        $message .= "Squid restart triggered. Output: " . htmlspecialchars($output) . " ";
    }

    /* -------------------------------------------------------------
       3. Bulk Update from Predefined Domain List Selection Processing
       ------------------------------------------------------------- */
    if (isset($_POST['update_selection'])) {

        // Get the array of selected categories (if any)
        $selectedCategories = [];
        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
            $selectedCategories = $_POST['categories'];
        }

        // Read the current ACL file content (if it exists)
        if (file_exists($aclFile)) {
            $content = file_get_contents($aclFile);
        } else {
            $content = "";
        }

        // Remove existing category blocks for all our managed categories
        foreach ($categories as $catKey => $dummy) {
            // Pattern matches the block from BEGIN to END (using dotall mode)
            $pattern = '/### BEGIN CATEGORY: ' . preg_quote($catKey, '/') . '\s*.*?\s*### END CATEGORY: ' . preg_quote($catKey, '/') . '\s*/s';
            $content = preg_replace($pattern, '', $content);
        }

        // For each selected category, read its list file and append a new block.
        foreach ($selectedCategories as $cat) {
            if (isset($categories[$cat])) {
                $listFile = $categories[$cat];
                if (file_exists($listFile) && is_readable($listFile)) {
                    $listContent = file_get_contents($listFile);
                    // Build the block string with markers.
                    $block = "### BEGIN CATEGORY: " . $cat . "\n" 
                           . trim($listContent) . "\n" 
                           . "### END CATEGORY: " . $cat . "\n";
                    // Append a newline and the block.
                    $content .= "\n" . $block;
                    $message .= "Added/updated category: " . htmlspecialchars($cat) . ". ";
                } else {
                    $message .= "Could not read file for category: " . htmlspecialchars($cat) . ". ";
                }
            }
        }

        // Write the updated content back to the ACL file.
        if (file_put_contents($aclFile, $content) !== false) {
            $message .= "Bulk update complete.";
        } else {
            $message .= "Error writing updated categories to file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Blocked Domains</title>
</head>
<body>
    <h1>Manage Blocked Domains</h1>
    
    <!-- Display any feedback messages -->
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- 1. Add Domain Form -->
    <h2>Add a Blocked Domain</h2>
    <form method="post" action="">
        <label for="domain">Domain:</label>
        <input type="text" id="domain" name="domain" placeholder="example.com" required>
        <input type="submit" name="add_domain" value="Add Domain">
    </form>

    <!-- 2. Update List (Restart Squid) Form -->
    <h2>Update List (Restart Squid)</h2>
    <form method="post" action="">
        <input type="submit" name="update_list" value="Update List">
    </form>

    <!-- 3. Bulk Update: Append/Remove Predefined Domain Lists -->
    <h2>Bulk Update: Predefined Domain Categories</h2>
    <form method="post" action="">
        <?php foreach ($categories as $key => $file): ?>
            <input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($key); ?>" id="<?php echo htmlspecialchars($key); ?>">
            <label for="<?php echo htmlspecialchars($key); ?>"><?php echo ucfirst($key); ?></label><br>
        <?php endforeach; ?>
        <input type="submit" name="update_selection" value="Update Selection">
    </form>
</body>
</html>
