const viewports = [
    'iphone-3',
    'iphone-5',
    'iphone-6',
    'iphone-xr',
    'ipad-2',
    'macbook-11',
    'macbook-13',
    'macbook-15',
    'macbook-16',
]

viewports.forEach((viewport) => {
  beforeEach(() => {
    cy.viewport(viewport)
    cy.visit('localhost:8081/')
  })

  describe(`home on ${viewport}`, () => {
    it('should contain headers', () => {
      cy.contains('php-dry')
      cy.contains('Syntactical and semantic clone detection for PHP in PHP')
    })

    it('should contain latest version information', () => {
      cy.contains('Latest version')
    })

    it('should contain sponsors', () => {
      cy.get('#sponsor-queo')
          .should('have.attr', 'href')
          .and('eq', 'https://queo.de')
    })

    const buttons = [
      {
        title: 'GitHub',
        url: 'https://github.com/leovie/php-dry',
      },
      {
        title: 'Documentation',
        url: '/documentation/index.html'
      },
      {
        title: 'Blog',
        url: '/blog.html'
      },
      {
        title: 'Leo Viezens',
        url: 'https://leovie.de'
      },
      {
        title: 'community',
        url: 'https://github.com/leovie/php-dry/graphs/contributors'
      },
    ]

    buttons.forEach((button) => {
      it(`should contain ${button.title} button`, () => {
        cy.contains(button.title)
            .should('have.attr', 'href')
            .and('eq', button.url)
      })
    })
  })
})