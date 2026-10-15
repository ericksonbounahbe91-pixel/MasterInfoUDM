import pymysql
pymysql.version_info = (2, 2, 1, "final", 0)  # évite le contrôle de version de Django
pymysql.install_as_MySQLdb()