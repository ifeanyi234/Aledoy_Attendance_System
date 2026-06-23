<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="attendsystem.css">
    <link rel="icon" href="Images/icon.png">
    <title>Aledoy Attendance</title>
</head>
<body>
    <div class="form-container">
        <div class="logo-badge">
            <img src="Images/images-removebg-preview.png" alt="Company Logo" class="company-logo">
        </div>
        
        <h2>Attendance</h2>
        
        <form action="#" method="POST">
            <div class="input-group">
                <input type="text" id="name" name="name" placeholder="Full Name" required>
            </div>
            
            <div class="input-group">
                <input type="email" id="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <select id="status" name="status" required>
                    <option value="" disabled selected>Select Status</option>
                    <option value="in">Clock In</option>
                    <option value="out">Clock Out</option>
                </select>
            </div>
            
            <button type="submit" class="submit-btn">SUBMIT</button>
        </form>
    </div>
</body>
</html>
    