<?php require('session.php');?>
<?php if(logged_in()){ ?>
    <script type="text/javascript">
        window.location = "index.php";
    </script>
<?php } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="TAKEOUTCOFFEE login portal">
    <meta name="author" content="">
    <title>TAKEOUTCOFFEE</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: {
                            lightest: '#F5E9D7', // Very light cream
                            lighter: '#E6C9A8', // Light cream
                            light: '#D4A76A',   // Caramel
                            DEFAULT: '#A05F2C', // Rich medium brown
                            medium: '#7D4B2A',  // Medium dark brown
                            dark: '#5D3921',    // Dark roast
                            darkest: '#2C1B10'  // Very dark espresso
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-coffee-dark to-coffee-darkest min-h-screen flex items-center justify-center p-5" style="font-family: 'Poppins', sans-serif;">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-coffee tracking-wider">TAKEOUTCOFFEE</h1>
                    <div class="mt-2 inline-block p-2 rounded-full bg-coffee-lighter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-coffee" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                            <line x1="6" y1="1" x2="6" y2="4"></line>
                            <line x1="10" y1="1" x2="10" y2="4"></line>
                            <line x1="14" y1="1" x2="14" y2="4"></line>
                        </svg>
                    </div>
                </div>
                
                <form class="space-y-6" role="form" action="processlogin.php" method="post">
                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-coffee-light">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input class="w-full pl-10 pr-4 py-3 bg-coffee-lightest border border-coffee-lighter rounded-lg focus:outline-none focus:ring-2 focus:ring-coffee-light focus:bg-white transition-all duration-200" 
                                placeholder="Username" name="user" type="text" autofocus>
                        </div>
                    </div>
                    
                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-coffee-light">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input class="w-full pl-10 pr-4 py-3 bg-coffee-lightest border border-coffee-lighter rounded-lg focus:outline-none focus:ring-2 focus:ring-coffee-light focus:bg-white transition-all duration-200" 
                                placeholder="Password" name="password" type="password">
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-gradient-to-r from-coffee-light to-coffee text-white font-medium rounded-lg shadow hover:from-coffee hover:to-coffee-medium focus:outline-none focus:ring-2 focus:ring-coffee focus:ring-offset-2 transition-all duration-200" 
                        type="submit" name="btnlogin">
                        Login
                    </button>
                </form>
                
                <div class="mt-8 text-center text-sm text-coffee-medium">
                    <p>Need assistance? Contact your administrator</p>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center text-xs text-coffee-lighter">
            <p>© <?php echo date('Y'); ?> TAKEOUTCOFFEE. All rights reserved.</p>  
        </div>
    </div>
</body>
</html>