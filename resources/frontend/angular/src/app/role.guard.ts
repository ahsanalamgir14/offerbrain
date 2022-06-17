import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class RoleGuard implements CanActivate {

  constructor(private router: Router) { }
  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    let role = route.data.roles as Array<string>;
    let permissions = route.data.permissions as Array<string>;
    console.log('permissions :', permissions);

    //for dev
    const fakeRole = 'user';
    const fakePermission = 'can-redirect';
    if (role.includes(fakeRole)) {
      // if (permissions.includes(fakePermission)) {
      return true;
      // }
      // else return false;
    }
    else return false;

    //for production
    // return fetch(`${environment.endpoint}/api/role`, {
    //   method: 'GET',
    //   headers: { "Content-type": "application/json; charset=UTF-8" },
    //   credentials: 'same-origin'
    // })
    //   .then(response => response.json())
    //   .then(data => {
    //     if (data.role == role) {
    //       return true;
    //     }
    //     this.router.navigate(['not-found']);
    //     return false;
    //   });
  }
}
