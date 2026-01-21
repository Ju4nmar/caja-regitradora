import { Routes } from '@angular/router';
import { Login } from './auth/login/login';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' as const },
  { path: 'login', component: Login },
];
