// Runs the actual Blade timer core in Chrome with controlled time/server snapshots.
// No Laravel/database/Pusher connection is needed. Regression tests with simulated API; not backend E2E.
// npm install --prefix /tmp/cotuong-timer-audit playwright
// NODE_PATH=/tmp/cotuong-timer-audit/node_modules node scripts/timer-lag-audit.cjs
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '..');
const blade = fs.readFileSync(path.join(root, 'resources/views/layouts/partials/timer.blade.php'), 'utf8');
const markup = blade.slice(0, blade.indexOf('<script>')).replace(/{{.*?}}/g, 'Audit');
const core = blade.split('<script>')[1].split("    document.addEventListener('DOMContentLoaded'")[0].replace(/{{.*?}}/g, 'audit');
const initialization=blade.split('<script>')[1].slice(blade.split('<script>')[1].indexOf("    document.addEventListener('DOMContentLoaded'")).split('</script>')[0].replace(/{{.*?}}/g,'audit');
const roomBlade=fs.readFileSync(path.join(root,'resources/views/layouts/room_master.blade.php'),'utf8');
const replaceBlade=source=>source.replace(/{{.*?}}/g,'audit');
const moveBlock=roomBlade.slice(roomBlade.indexOf('        function updateFenCode('),roomBlade.indexOf("        if (typeof Echo !== 'undefined')"));
const playerMove=replaceBlade('function updateFenCode(roomCode,moveIccs) {\n'+moveBlock.split('@else')[1].split('@endif')[0]+'\n}');
const boardRecovery=replaceBlade(roomBlade.slice(roomBlade.indexOf("        window.addEventListener('room:timer-snapshot'"),roomBlade.indexOf("        @if ($role === 'watch')",roomBlade.indexOf('let boardRevision'))));

(async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROME_PATH || (process.platform === 'darwin'
      ? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' : undefined),
    headless: true,
  });
  const results = [];
  async function scenario(name, run, controlled = true) {
    const page = await browser.newPage();
    await page.route('**/*', route => route.abort());
    await page.setContent(markup);
    if (controlled) await page.evaluate(() => {
      window.auditNow = 0;
      Date.now = () => window.auditNow;
      performance.now = () => window.auditNow;
      window.setInterval = () => 1;
      window.clearInterval = () => {};
    });
    else await page.evaluate(() => {
      window.tickCosts=[];
      const originalInterval=window.setInterval.bind(window);
      window.setInterval=(callback, delay)=>originalInterval(()=>{
        const t=performance.now(); callback(); tickCosts.push(performance.now()-t);
      },delay);
    });
    await page.addScriptTag({ content: core + `
      window.audit = {
        sync: syncTimerState, tick: updateUI, refresh: refreshTimer,
        state: () => ({ time: {...time}, activePlayer, serverMoveElapsed, isGameOver,
          remaining: time.red - (activePlayer === 'red' ? Math.max(0, performance.now()-localLastUpdate)/1000 : 0),
          red: ui.red.clock.innerText, black: ui.black.clock.innerText,
          move: ui.red.moveClock.innerText })
      };` });
    const details = await run(page);
    results.push({ name, ...details });
    await page.close();
  }
  try {
    await scenario('normal countdown without synchronization', async page => {
      const observed = await page.evaluate(() => {
        audit.sync({red_time:600, black_time:600, active_player:'red', move_elapsed:0});
        auditNow = 5000; audit.tick(); return audit.state();
      });
      assert.equal(observed.red, '9:55');
      return { status: 'passed', observed };
    });
    await scenario('sync after 1 second preserves countdown', async page => {
      const observed = await page.evaluate(() => {
        audit.sync({red_time:600, black_time:600, active_player:'red', move_elapsed:0});
        auditNow = 1000; audit.tick(); const before = audit.state();
        audit.sync({red_time:599, black_time:600, active_player:'red', move_elapsed:1});
        return {before, after:audit.state()};
      });
      assert.equal(observed.before.red, '9:59');
      assert.equal(observed.after.red, '9:59');
      return { status: 'passed', expected: '9:59 after sync', observed };
    });
    await scenario('frequent sync preserves elapsed time', async page => {
      const observed = await page.evaluate(() => {
        audit.sync({red_time:600, black_time:600, active_player:'red', move_elapsed:0});
        return [500,1000,1500,2000].map(ms => {
          auditNow=ms;
          audit.sync({red_time:600-ms/1000, black_time:600, active_player:'red', move_elapsed:ms/1000});
          return {ms, red:audit.state().red, move:audit.state().move};
        });
      });
      assert.deepEqual(observed.map(x => x.red), ['10:00','9:59','9:59','9:58']);
      return { status: 'passed', observed };
    });
    await scenario('paused:red holds total and move clocks', async page => {
      const observed = await page.evaluate(() => {
        audit.sync({red_time:500, black_time:600, active_player:'paused:red', move_elapsed:30});
        auditNow=5000; audit.tick(); return audit.state();
      });
      assert.equal(observed.red, '8:20');
      assert.equal(observed.move, '1:30');
      assert.equal(observed.activePlayer, null);
      return { status: 'passed', expected: '8:20 and stopped', observed };
    });
    await scenario('older revision cannot roll a confirmed turn back', async page => {
      const observed = await page.evaluate(async () => {
        window.fetch = async () => ({ok:true, json:async () => ({red_time:599,black_time:600,active_player:'black',move_elapsed:0,timer_revision:1})});
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0});
        auditNow=1000;
        await switchTurn('audit','red');
        await Promise.resolve(); await Promise.resolve(); await Promise.resolve();
        const before=audit.state();
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:1,timer_revision:0});
        return {before, after:audit.state()};
      });
      assert.equal(observed.before.activePlayer, 'black');
      assert.equal(observed.after.activePlayer, 'black');
      return { status: 'passed', expected: 'black remains active', observed };
    });
    await scenario('delayed event accounts for snapshot age after HTTP calibration', async page => {
      const observed = await page.evaluate(() => {
        auditNow=20;
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,
          snapshot_at:1000010,_sentAt:0,_receivedAt:20});
        auditNow=820;
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,snapshot_at:1000020});
        auditNow=1820; audit.tick(); return audit.state();
      });
      assert.equal(observed.remaining,598.2);
      return {status:'passed', expectedRemaining:598.2, observed};
    });
    await scenario('local expiry accepts server correction without submitting a result', async page => {
      const observed = await page.evaluate(() => {
        window.fetch=()=>new Promise(()=>{});
        window.results=[]; window.updateResult=(...args)=>results.push(args);
        audit.sync({red_time:1,black_time:600,active_player:'red',move_elapsed:0});
        auditNow=1000; audit.tick();
        audit.sync({red_time:5,black_time:600,active_player:'red',move_elapsed:0});
        return {...audit.state(),submitted:results};
      });
      assert.equal(observed.isGameOver,false);
      assert.equal(observed.red,'0:05');
      assert.equal(observed.submitted.length,0);
      return { status:'passed', expected:'server correction should be accepted before finalizing', observed };
    });
    await scenario('real browser interval and DOM update cost', async page => {
      await page.evaluate(() => {
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0});
      });
      await page.waitForTimeout(3200);
      const observed=await page.evaluate(()=>({
        ...audit.state(), ticks:tickCosts.length,
        meanTickMs:tickCosts.reduce((a,b)=>a+b,0)/tickCosts.length,
        maxTickMs:Math.max(...tickCosts),
      }));
      assert.equal(observed.red,'9:57');
      return { status:'passed', observed };
    }, false);
    await scenario('older GET started before a move cannot overwrite it', async page => {
      const observed = await page.evaluate(async () => {
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        let release;
        window.fetch = url => url.includes('getTime')
          ? new Promise(resolve => { release=()=>resolve({ok:true,json:async()=>({red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0})}); })
          : Promise.resolve({ok:true,json:async()=>({red_time:599,black_time:600,active_player:'black',move_elapsed:0,timer_revision:1})});
        const oldGet=audit.refresh();
        auditNow=1000;
        await switchTurn('audit','red');
        release(); await oldGet;
        return audit.state();
      });
      assert.equal(observed.activePlayer,'black');
      return {status:'passed',observed};
    });
    await scenario('atomic move submits one request with FEN and current player', async page => {
      const observed = await page.evaluate(async () => {
        window.calls=[];
        window.fetch=async (url, options)=>{
          calls.push({url,body:JSON.parse(options.body)});
          return {ok:true,json:async()=>({success:true,fen:'new-fen',red_time:599,black_time:600,active_player:'black',move_elapsed:0,timer_revision:1})};
        };
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        auditNow=1000;
        await switchTurn('audit','red',{'ma-phong':'audit',FEN:'new-fen',expected_fen:'old-fen',move:'a3a4'});
        return {state:audit.state(),calls};
      });
      assert.equal(observed.calls.length,1);
      assert.equal(observed.calls[0].body.current_player,'red');
      assert.equal(observed.calls[0].body.expected_fen,'old-fen');
      assert.match(observed.calls[0].url,/updateFEN$/);
      assert.equal(observed.state.activePlayer,'black');
      return {status:'passed',observed};
    });
    await scenario('pending move rejects unrelated event until response arrives', async page => {
      const observed = await page.evaluate(async () => {
        let release;
        window.fetch=()=>new Promise(resolve=>{release=()=>resolve({ok:true,json:async()=>({success:true,fen:'new',red_time:599,black_time:600,active_player:'black',move_elapsed:0,timer_revision:1})});});
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        const move=switchTurn('audit','red',{'ma-phong':'audit',FEN:'new',expected_fen:'old'});
        const staleAccepted=audit.sync({fen:'old',red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        const canMove=canSubmitRoomMove('black');
        release(); await move;
        return {staleAccepted,canMove,state:audit.state()};
      });
      assert.equal(observed.staleAccepted,false);
      assert.equal(observed.canMove,false);
      assert.equal(observed.state.activePlayer,'black');
      return {status:'passed',observed};
    });
    await scenario('a rejected move restores the authoritative timer', async page => {
      const observed = await page.evaluate(async () => {
        window.fetch=async()=>({ok:true,json:async()=>({success:false,fen:'old',red_time:598,black_time:600,active_player:'red',move_elapsed:2,timer_revision:0})});
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        const response=await switchTurn('audit','red',{'ma-phong':'audit',FEN:'new',expected_fen:'old'});
        return {response,state:audit.state()};
      });
      assert.equal(observed.response.success,false);
      assert.equal(observed.state.activePlayer,'red');
      assert.equal(observed.state.red,'9:58');
      return {status:'passed',observed};
    });
    await scenario('only server-confirmed expiry submits one timeout result', async page => {
      const observed=await page.evaluate(()=>{
        window.results=[]; window.updateResult=(...args)=>results.push(args);
        audit.sync({red_time:0,black_time:600,active_player:'red',move_elapsed:120,timed_out:true});
        audit.tick();
        audit.sync({red_time:0,black_time:600,active_player:'red',move_elapsed:120,timed_out:true});
        return {...audit.state(),submitted:results};
      });
      assert.equal(observed.isGameOver,true);
      assert.deepEqual(observed.submitted,[['audit','-1',true]]);
      return {status:'passed',observed};
    });
    await scenario('expiry polling never overlaps requests', async page => {
      const observed=await page.evaluate(()=>{
        window.calls=0;window.fetch=()=>{calls++;return new Promise(()=>{});};
        audit.sync({red_time:1,black_time:600,active_player:'red',move_elapsed:0});
        [1000,2000,3000,4000].forEach(ms=>{auditNow=ms;audit.tick();});
        return {calls,state:audit.state()};
      });
      assert.equal(observed.calls,1);
      assert.equal(observed.state.isGameOver,false);
      return {status:'passed',observed};
    });
    await scenario('invalid snapshot does not corrupt the clocks', async page => {
      const observed=await page.evaluate(()=>{
        audit.sync({red_time:600,black_time:600,active_player:'red',move_elapsed:0});
        const accepted=audit.sync({red_time:'invalid',black_time:600,active_player:'red'});
        return {accepted,state:audit.state()};
      });
      assert.equal(observed.accepted,false);
      assert.equal(observed.state.red,'10:00');
      return {status:'passed',observed};
    });
    await scenario('rejected move restores the real room board handler', async page => {
      await page.addScriptTag({content:`
        let currentFEN='old', expectedFEN=null, boardRevision=0, kypho=null;
        const game={value:'old',fen(){return this.value},load(fen){this.value=fen},turn(){return 'b'},game_over(){return false}};
        const board={position(fen){window.lastPosition=fen}};
        function playMoveResultSound(){} function updateStatus(){}
        ${boardRecovery}\n${playerMove}`});
      const observed=await page.evaluate(async()=>{
        window.calls=[];
        window.fetch=async(url,options)=>{
          calls.push({url,body:JSON.parse(options.body)});
          return {ok:true,json:async()=>({success:false,fen:'old',red_time:598,black_time:600,active_player:'red',move_elapsed:2,timer_revision:0})};
        };
        audit.sync({fen:'old',red_time:600,black_time:600,active_player:'red',move_elapsed:0,timer_revision:0});
        game.value='new';
        await updateFenCode('audit','a3a4');
        return {fen:game.fen(),expectedFEN,currentFEN,calls};
      });
      assert.equal(observed.fen,'old');
      assert.equal(observed.currentFEN,'old');
      assert.equal(observed.expectedFEN,null);
      assert.equal(observed.calls.length,1);
      assert.equal(observed.calls[0].body.expected_fen,'old');
      return {status:'passed',observed};
    });
    await scenario('failed timeout confirmation resumes polling with backoff', async page=>{
      const observed=await page.evaluate(async()=>{
        window.submitted=[];window.updateResult=(...args)=>submitted.push(args);
        audit.sync({red_time:0,black_time:600,active_player:'red',move_elapsed:120,timed_out:true});
        window.fetch=async()=>({ok:true,json:async()=>({red_time:0,black_time:600,active_player:'red',move_elapsed:120,timed_out:true})});
        await resumeRoomTimer();
        return {...audit.state(),submitted};
      });
      assert.equal(observed.isGameOver,false);
      assert.equal(observed.submitted.length,1);
      return {status:'passed',observed};
    });
    await scenario('actual initialization starts anonymous match and handles events without extra GET', async page=>{
      await page.evaluate(()=>{
        window.calls=[];window.started=false;window.registered=[];
        window.setInterval=(fn,delay)=>{registered.push({fn,delay});return registered.length;};
        window.fetch=async(url)=>{
          calls.push(url);
          if(url.endsWith('getRoomIds')) return {ok:true,json:async()=>({has_host:true,has_guest:true})};
          if(url.includes('startMatch')) started=true;
          return {ok:true,json:async()=>({red_time:600,black_time:600,active_player:started?'red':'waiting',move_elapsed:0,timer_revision:started?1:0})};
        };
        window.Echo={
          join:()=>({here(){return this},joining(){return this},leaving(){return this}}),
          channel:()=>({listen(name,fn){window.roomEvent=fn;return this}}),
          connector:{pusher:{connection:{bind(name,fn){window.reconnect=fn}}}},
        };
      });
      await page.addScriptTag({content:initialization});
      await page.evaluate(()=>document.dispatchEvent(new Event('DOMContentLoaded')));
      await page.waitForFunction(()=>audit.state().activePlayer==='red');
      const observed=await page.evaluate(async()=>{
        const getsBefore=calls.filter(url=>url.includes('getTime')).length;
        roomEvent({room:{fen:'same'},timer:{red_time:599,black_time:600,active_player:'red',move_elapsed:1,timer_revision:1}});
        const getsAfter=calls.filter(url=>url.includes('getTime')).length;
        await reconnect();
        return {getsBefore,getsAfter,getsAfterReconnect:calls.filter(url=>url.includes('getTime')).length,
          started,registeredDelays:registered.map(x=>x.delay),state:audit.state()};
      });
      assert.equal(observed.started,true);
      assert.equal(observed.getsAfter,observed.getsBefore);
      assert.equal(observed.getsAfterReconnect,observed.getsBefore+1);
      assert.ok(observed.registeredDelays.includes(15000));
      assert.equal(observed.state.activePlayer,'red');
      return {status:'passed',observed};
    });
    const output={ browser:await browser.version(), scope:'actual timer core and DOM; simulated API/time except real interval baseline; no backend E2E', results };
    fs.writeFileSync(path.join(root,'docs/timer-sync-test-results.json'), JSON.stringify(output,null,2)+'\n');
    console.log(JSON.stringify(output,null,2));
  } finally { await browser.close(); }
})().catch(err=>{console.error(err);process.exitCode=1;});
