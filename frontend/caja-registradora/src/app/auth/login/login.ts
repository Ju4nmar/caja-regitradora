import { Component } from '@angular/core';
import { Auth } from '../../core/services/auth';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  templateUrl: './login.html',
  styleUrl: './login.css',
  standalone: true,
  imports: [FormsModule]
})
export class Login {

  username = '';
  password = '';

  constructor(private auth: Auth) {}

  async submit() {
    const res = await this.auth.login({
      username: this.username,
      password: this.password
    });

    const data = await res.json();
    console.log(data);

    if (res.ok) {
      localStorage.setItem('token', data.token);
      localStorage.setItem('rol', data.rol);
    }
  }
}
