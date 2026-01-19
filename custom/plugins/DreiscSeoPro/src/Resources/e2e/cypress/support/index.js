//Write protected foundation commands
import './foundation/commands'

//Plugin commands
import './commands'

before(() => {
    return cy.syncAndActivateE2eDatabase();
});

after(function() {
    return cy.resetEnvDatabase();
})
