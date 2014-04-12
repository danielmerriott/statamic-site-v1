# Create htpasswd file
htpasswd -Bb -c /data/www/openleaf-co-nz/config/htpasswd daniel letmeinnow
# Append additional users to htpasswd file
htpasswd -Bb /data/www/openleaf-co-nz/config/htpasswd mary-anne letmeinnow
htpasswd -Bb /data/www/openleaf-co-nz/config/htpasswd josie cowriter
