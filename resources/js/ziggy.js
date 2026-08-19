const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"home":{"uri":"\/","methods":["GET","HEAD"]},"login":{"uri":"login\/{role}","methods":["GET","HEAD"],"wheres":{"role":"administrator|teacher|student"},"parameters":["role"]},"register":{"uri":"register\/{role}","methods":["GET","HEAD"],"wheres":{"role":"administrator|teacher|student"},"parameters":["role"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
