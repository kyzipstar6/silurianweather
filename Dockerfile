# Use official PHP CLI image
FROM php:8.2-cli

# Render checks out your code into /opt/render/project/src by default,
# but we'll set a normal working directory
WORKDIR /opt/render/project/src

# Copy everything into the container
COPY . .

# Run the built-in PHP server
# Render sets $PORT, but locally we default to 10000
CMD ["sh", "-c", "npm install php", "php -S 0.0.0.0:${PORT:-10000} -t public"]
